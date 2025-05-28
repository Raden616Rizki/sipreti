import csv
import io
import json
import numpy as np
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from .models import VektorPegawai
from .face_verification.extraction import face_extraction_gdrive, extract_folder_id, face_extraction
from voyager import Index, Space
import time
from scipy.spatial import distance
import requests
from django.conf import settings
from .progress_tracker import set_progress, get_progress
from collections import defaultdict
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score, roc_curve, auc
from itertools import combinations
import random
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
from matplotlib.ticker import MultipleLocator
import base64
import cv2
from io import BytesIO
from PIL import Image
from datetime import datetime

@csrf_exempt
def upload_csv(request):
    if request.method == "POST" and request.FILES.get("file"):
        csv_file = request.FILES["file"]
        task_id = request.GET.get("task_id", "default")

        try:
            raw_data = csv_file.read().decode("utf-8").splitlines()
            reader = csv.DictReader(raw_data)

            required_columns = {'id_pegawai', 'url_photo_folder'}
            if not required_columns.issubset(reader.fieldnames):
                return JsonResponse({
                    'error': 'CSV Tidak Valid. Pastikan terdapat kolom: id_pegawai dan url_photo_folder.'
                }, status=400)

            rows = list(reader)
            total_rows = len(rows)
            current = 0

            for row in rows:
                id_pegawai = row.get("id_pegawai")
                folder_url = row.get("url_photo_folder")

                if not id_pegawai or not folder_url:
                    current += 1
                    if task_id:
                        set_progress(task_id, current, total_rows)
                    continue

                folder_id = extract_folder_id(folder_url)
                if not folder_id:
                    print(f"URL folder tidak valid: {folder_url}")
                    current += 1
                    if task_id:
                        set_progress(task_id, current, total_rows)
                    continue

                results = face_extraction_gdrive(folder_id, id_pegawai)
                if results:
                    vectors, originalImages = results
                    print(f"Berhasil mengekstrak {len(vectors)} vektor wajah untuk ID pegawai {id_pegawai}")

                    for i, vector in enumerate(vectors):
                        try:
                            image_file = originalImages[i]
                            image_file.seek(0)
                            files = {
                                'url_foto': (image_file.name, image_file, 'image/jpeg')
                            }
                            data = {
                                'id_pegawai': id_pegawai,
                                'face_embeddings': json.dumps(vector)
                            }

                            response = requests.post(settings.CI3_API_URL, data=data, files=files)

                            if response.status_code == 200:
                                print(f"Data berhasil dikirim untuk ID Pegawai {id_pegawai}, foto {i+1}")
                            else:
                                print(f"Error response dari CI3: {response.text}")

                        except Exception as send_err:
                            print(f"Gagal mengirim data ke CI3: {send_err}")
                else:
                    print(f"Tidak ada vektor wajah yang diekstrak untuk ID pegawai {id_pegawai}")

                current += 1
                if task_id:
                    set_progress(task_id, current, total_rows)

            return JsonResponse({'message': 'Data Berhasil Diproses'}, status=200)

        except UnicodeDecodeError:
            return JsonResponse({'error': 'File tidak dapat dibaca. Pastikan file berformat UTF-8.'}, status=400)
        except csv.Error:
            return JsonResponse({'error': 'Format CSV Tidak Valid.'}, status=400)

    return JsonResponse({'error': 'Invalid request. Harus POST dan mengandung file CSV.'}, status=400)

@csrf_exempt
def upload_csv_pegawai(request):
    if request.method == "POST" and request.FILES.get("file"):
        csv_file = request.FILES["file"]
        task_id = request.GET.get("task_id", "default")

        try:
            decoded_file = csv_file.read().decode("utf-8")
            io_string = io.StringIO(decoded_file)

            reader = csv.DictReader(io_string, delimiter=';')

            required_columns = {'nip', 'nama', 'id_jabatan', 'id_unit_kerja', 'url_photo_folder'}
            if not required_columns.issubset(reader.fieldnames):
                return JsonResponse({
                    'error': 'CSV harus memiliki kolom: nip, nama, id_jabatan, id_unit_kerja, url_photo_folder'
                }, status=400)

            rows = list(reader)
            total_rows = len(rows)
            current = 0

            for row in rows:
                nip = row.get("nip")
                nama = row.get("nama")
                id_jabatan = row.get("id_jabatan")
                id_unit_kerja = row.get("id_unit_kerja")
                folder_url = row.get("url_photo_folder")

                if not all([nip, nama, id_jabatan, id_unit_kerja]):
                    current += 1
                    if task_id:
                        set_progress(task_id, current, total_rows)
                    continue

                url_foto = None
                vectors = []
                originalImages = []

                if folder_url:
                    folder_id = extract_folder_id(folder_url)
                    if folder_id:
                        results = face_extraction_gdrive(folder_id, nip)
                        if results:
                            vectors, originalImages = results
                            print(f"Berhasil mengekstrak {len(vectors)} wajah untuk NIP {nip}")
                            if originalImages:
                                url_foto = originalImages[0]
                        else:
                            print(f"Gagal ekstraksi wajah dari folder {folder_url} untuk NIP {nip}")
                    else:
                        print(f"URL folder tidak valid: {folder_url}")
                else:
                    print(f"Tidak ada URL folder foto untuk NIP {nip}")

                try:
                    files_pegawai = {}
                    if url_foto:
                        files_pegawai['url_foto'] = url_foto

                    data_pegawai = {
                        'nip': nip,
                        'nama': nama,
                        'id_jabatan': id_jabatan,
                        'id_unit_kerja': id_unit_kerja,
                    }

                    pegawai_response = requests.post(
                        settings.CI3_API_PEGAWAI_URL,
                        data=data_pegawai,
                        files=files_pegawai if files_pegawai else None
                    )

                    if pegawai_response.status_code == 200:
                        print(f"Data pegawai berhasil dikirim: {nip}")

                        if vectors and originalImages:
                            try:
                                response_data = pegawai_response.json()
                                id_pegawai = response_data.get("id_pegawai")

                                if not id_pegawai:
                                    print(f"Gagal ambil id_pegawai dari response untuk NIP {nip}")
                                    current += 1
                                    if task_id:
                                        set_progress(task_id, current, total_rows)
                                    continue

                                for i, vector in enumerate(vectors):
                                    try:
                                        image_file = originalImages[i]
                                        image_file.seek(0)
                                        files_vector = {
                                            'url_foto': (image_file.name, image_file, 'image/jpeg')
                                        }
                                        data_vector = {
                                            'id_pegawai': id_pegawai,
                                            'face_embeddings': json.dumps(vector)
                                        }

                                        vektor_response = requests.post(
                                            settings.CI3_API_URL,
                                            data=data_vector,
                                            files=files_vector
                                        )

                                        if vektor_response.status_code == 200:
                                            print(f"Vektor ke-{i+1} untuk {nip} berhasil dikirim")
                                        else:
                                            print(f"CI3 Gagal (vektor): {vektor_response.status_code} - {vektor_response.text}")

                                    except Exception as send_err:
                                        print(f"Gagal kirim vektor wajah {nip}: {send_err}")
                            except Exception as parse_err:
                                print(f"Error parsing response JSON dari CI3: {parse_err}")
                    else:
                        print(f"CI3 Gagal (pegawai): {pegawai_response.status_code} - {pegawai_response.text}")

                except Exception as e:
                    print(f"Gagal mengirim data pegawai {nip}: {e}")

                current += 1
                if task_id:
                    set_progress(task_id, current, total_rows)

            return JsonResponse({'message': 'Upload dan ekstraksi pegawai selesai'}, status=200)

        except Exception as e:
            return JsonResponse({'error': str(e)}, status=500)

    return JsonResponse({'error': 'Request harus POST dengan file'}, status=400)

@csrf_exempt
def face_register(request):
    if request.method == 'POST':
        id_pegawai = request.POST.get('id_pegawai')
        uploaded_file = request.FILES.get('uploaded_file')

        if not id_pegawai or not uploaded_file:
            return JsonResponse({'error': 'id_pegawai dan file foto wajib diisi'}, status=400)

        try:
            result = face_extraction(uploaded_file, id_pegawai)
            if not result:
                return JsonResponse({'error': 'Wajah tidak berhasil dideteksi'}, status=422)

            vector, original_io = result
            
            files = {
                'url_foto': original_io
            }
            data = {
                'id_pegawai': id_pegawai,
                'face_embeddings': json.dumps(vector) 
            }

            ci3_url = settings.CI3_API_URL
            response = requests.post(ci3_url, data=data, files=files)

            if response.status_code == 200:
                return JsonResponse({'message': 'Data berhasil dikirim ke CI3'}, status=200)
            else:
                return JsonResponse({'error': f"Error dari CI3: {response.text}"}, status=response.status_code)

        except Exception as e:
            return JsonResponse({'error': f'Gagal memproses: {e}'}, status=500)

    return JsonResponse({'error': 'Metode harus POST'}, status=405)

@csrf_exempt
def face_verification(request):
    if request.method == "POST":
        try:
            # Parsing request JSON
            data = json.loads(request.body)
            
            id_pegawai = data.get("id_pegawai")
            vektor_presensi = np.array(data.get("vektor_presensi"), dtype=np.float32)

            if not id_pegawai or vektor_presensi is None:
                return JsonResponse({"error": "id_pegawai dan vektor_presensi wajib diisi"}, status=400)

            # Ambil face embeddings dari database berdasarkan id_pegawai dan deleted_at kosong
            embeddings_data = VektorPegawai.objects.filter(id_pegawai=id_pegawai, deleted_at__isnull=True).values_list("face_embeddings", flat=True)

            if not embeddings_data:
                return JsonResponse({"error": "Data face embeddings tidak ditemukan"}, status=404)

            # Konversi face embeddings dari JSON string ke numpy array
            embeddings = [np.array(json.loads(embedding), dtype=np.float32) for embedding in embeddings_data]
        
            distances = [float(distance.cityblock(vektor_presensi, embedding)) for embedding in embeddings]
            
            # Cek apakah ada jarak di bawah 1 (threshold)
            verifikasi = any(d < 7 for d in distances)
            pesan = "Wajah terverifikasi" if verifikasi else "Wajah tidak terverifikasi"
            value = 1 if verifikasi else 0

            return JsonResponse({
                "id_pegawai": id_pegawai,
                "jarak": distances,
                "pesan": pesan,
                "value": value
            })


        except Exception as e:
            return JsonResponse({"error": str(e)}, status=500)

    return JsonResponse({"error": "Metode tidak diizinkan"}, status=405)

@csrf_exempt
def evaluate_face_recognition(request):
    queryset = VektorPegawai.objects.filter(deleted_at__isnull=True)
    embeddings = []
    labels = []
    label_counts = defaultdict(int)

    for obj in queryset:
        try:
            vector = json.loads(obj.face_embeddings)
            if isinstance(vector, list):
                embeddings.append(vector)
                labels.append(obj.id_pegawai)
                label_counts[obj.id_pegawai] += 1
        except json.JSONDecodeError:
            continue

    embeddings = np.array(embeddings)
    labels = np.array(labels)

    results = {
        "statistics": {
            "total_embeddings": len(embeddings),
            "total_labels": len(set(labels)),
            "embeddings_per_label": dict(label_counts)
        },
        "manhattan": {},
        "euclidean": {},
        "plots": {}
    }

    def build_pairs_and_evaluate(method_name, method, thresholds):
        metrics_by_threshold = {
            "accuracy": [], "precision": [], "recall": [], "f1": [], "thresholds": []
        }

        for threshold in thresholds:
            grouped = defaultdict(list)
            for i, label in enumerate(labels):
                grouped[label].append(embeddings[i])

            pos_pairs = []
            neg_pairs = []

            for vectors in grouped.values():
                if len(vectors) < 2:
                    continue
                for a, b in combinations(vectors, 2):
                    pos_pairs.append((a, b, 1))  # match

            label_list = list(grouped.keys())
            for _ in range(len(pos_pairs)):
                id1, id2 = random.sample(label_list, 2)
                a = random.choice(grouped[id1])
                b = random.choice(grouped[id2])
                neg_pairs.append((a, b, 0))  # non-match

            pairs = pos_pairs + neg_pairs
            y_true = []
            y_pred = []

            TP = FP = TN = FN = 0

            for a, b, label in pairs:
                dist = method(a, b)
                pred = 1 if dist < threshold else 0
                y_true.append(label)
                y_pred.append(pred)

                if label == 1 and pred == 1:
                    TP += 1
                elif label == 0 and pred == 1:
                    FP += 1
                elif label == 0 and pred == 0:
                    TN += 1
                elif label == 1 and pred == 0:
                    FN += 1

            acc = accuracy_score(y_true, y_pred)
            prec = precision_score(y_true, y_pred, zero_division=0)
            rec = recall_score(y_true, y_pred, zero_division=0)
            f1 = f1_score(y_true, y_pred, zero_division=0)

            results[method_name][str(threshold)] = {
                "accuracy": round(acc, 4),
                "precision": round(prec, 4),
                "recall": round(rec, 4),
                "f1": round(f1, 4),
                "true_positive": TP,
                "false_positive": FP,
                "true_negative": TN,
                "false_negative": FN,
                "total_pairs": len(pairs)
            }

            metrics_by_threshold["thresholds"].append(threshold)
            metrics_by_threshold["accuracy"].append(acc)
            metrics_by_threshold["precision"].append(prec)
            metrics_by_threshold["recall"].append(rec)
            metrics_by_threshold["f1"].append(f1)

        # Generate plot
        fig, ax = plt.subplots()
        ax.plot(metrics_by_threshold["thresholds"], metrics_by_threshold["accuracy"], label="Accuracy", marker='o', color="orange")
        ax.plot(metrics_by_threshold["thresholds"], metrics_by_threshold["precision"], label="Precision", marker='o', color="orangered")
        ax.plot(metrics_by_threshold["thresholds"], metrics_by_threshold["recall"], label="Recall", marker='o', color="crimson")
        ax.plot(metrics_by_threshold["thresholds"], metrics_by_threshold["f1"], label="F1 Score", marker='o', color="deeppink")
        ax.set_xlabel("Threshold")
        ax.set_ylabel("Score")
        ax.set_title(f"{method_name.capitalize()} - Metric Scores vs Threshold")
        ax.legend()
        ax.grid(True)

        buffer = io.BytesIO()
        plt.savefig(buffer, format='png')
        plt.close(fig)
        buffer.seek(0)
        img_base64 = base64.b64encode(buffer.read()).decode('utf-8')
        results["plots"][method_name] = img_base64

    # build_pairs_and_evaluate("manhattan", distance.cityblock, [3, 4, 5, 6, 7, 8, 9])
    build_pairs_and_evaluate("euclidean", distance.euclidean, [0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9])

    sorted_items = sorted(label_counts.items())  # urutkan berdasarkan label
    keys = [str(k) for k, v in sorted_items]     # label pegawai sebagai string
    vals = [v for k, v in sorted_items]

    # Ukuran figure dinamis mengikuti jumlah label
    fig, ax = plt.subplots(figsize=(max(12, len(keys) * 0.3), 6))

    # Plot bar chart
    ax.bar(keys, vals, color='skyblue')

    # Label dan judul
    ax.set_title("Jumlah Embeddings per Label Pegawai", fontsize=16)
    ax.set_xlabel("Label Pegawai", fontsize=12)
    ax.set_ylabel("Jumlah Embeddings", fontsize=12)

    # Rotasi dan ukuran label X agar terbaca
    plt.xticks(rotation=90, fontsize=8)

    # Tambah garis bantu horizontal
    ax.grid(axis='y', linestyle='--', alpha=0.7)
    ax.yaxis.set_major_locator(MultipleLocator(1))

    # Tata letak otomatis agar tidak terpotong
    plt.tight_layout()

    buffer = io.BytesIO()
    plt.savefig(buffer, format='png')
    plt.close(fig)
    buffer.seek(0)
    embedding_dist_base64 = base64.b64encode(buffer.read()).decode('utf-8')
    results["plots"]["embeddings_per_label"] = embedding_dist_base64

    return JsonResponse(results, json_dumps_params={"indent": 2})

@csrf_exempt
def evaluate_roc_curve(request):
    queryset = VektorPegawai.objects.filter(deleted_at__isnull=True)
    embeddings = []
    labels = []
    grouped = defaultdict(list)

    for obj in queryset:
        try:
            vector = json.loads(obj.face_embeddings)
            if isinstance(vector, list):
                embeddings.append(vector)
                labels.append(obj.id_pegawai)
                grouped[obj.id_pegawai].append(vector)
        except json.JSONDecodeError:
            continue

    def get_pairs():
        pos_pairs = []
        neg_pairs = []
        label_list = list(grouped.keys())

        for vectors in grouped.values():
            if len(vectors) < 2:
                continue
            for a, b in combinations(vectors, 2):
                pos_pairs.append((a, b, 1))

        for _ in range(len(pos_pairs)):
            id1, id2 = random.sample(label_list, 2)
            a = random.choice(grouped[id1])
            b = random.choice(grouped[id2])
            neg_pairs.append((a, b, 0))

        return pos_pairs + neg_pairs

    def encode_plot_to_base64(fig):
        buf = io.BytesIO()
        plt.savefig(buf, format='png', bbox_inches='tight')
        plt.close(fig)
        buf.seek(0)
        return f"data:image/png;base64,{base64.b64encode(buf.read()).decode('utf-8')}"

    def compute_roc(method_name, method):
        pairs = get_pairs()
        y_true = []
        y_scores = []

        for a, b, label in pairs:
            dist = method(a, b)
            score = -dist  # negative distance = higher similarity
            y_true.append(label)
            y_scores.append(score)

        fpr, tpr, thresholds = roc_curve(y_true, y_scores)
        roc_auc = auc(fpr, tpr)

        # Gunakan threshold optimal berdasarkan jarak minimum ke titik (0, 1)
        distances = np.sqrt((1 - tpr) ** 2 + fpr ** 2)
        optimal_idx = np.argmin(distances)
        optimal_threshold = thresholds[optimal_idx]

        # --- ROC Curve ---
        fig1, ax1 = plt.subplots()
        ax1.plot(fpr, tpr, color='darkorange', lw=2, label=f'ROC curve (AUC = {roc_auc:.4f})')
        ax1.plot([0, 1], [0, 1], color='navy', lw=1, linestyle='--')
        ax1.set_xlim([0.0, 1.0])
        ax1.set_ylim([0.0, 1.05])
        ax1.set_xlabel('False Positive Rate')
        ax1.set_ylabel('True Positive Rate')
        ax1.set_title(f'Receiver Operating Characteristic - {method_name}')
        ax1.legend(loc="lower right")
        roc_curve_base64 = encode_plot_to_base64(fig1)

        # --- TPR/FPR vs Threshold ---
        fig2, ax2 = plt.subplots()
        ax2.plot(-thresholds, tpr, label='True Positive Rate (TPR)')
        ax2.plot(-thresholds, fpr, label='False Positive Rate (FPR)')
        ax2.axvline(x=-optimal_threshold, color='red', linestyle='--', label='Optimal Threshold')
        ax2.set_xlabel('Threshold (Distance)')
        ax2.set_ylabel('Rate')
        ax2.set_title(f'TPR / FPR vs Threshold - {method_name}')
        ax2.legend()
        threshold_plot_base64 = encode_plot_to_base64(fig2)

        return {
            "auc": round(roc_auc, 4),
            "optimal_threshold": round(-optimal_threshold, 4),  # kembali ke nilai distance (positif)
            "roc_curve_image": roc_curve_base64,
            "threshold_plot_image": threshold_plot_base64
        }

    results = {
        "manhattan": compute_roc("Manhattan", distance.cityblock),
        "euclidean": compute_roc("Euclidean", distance.euclidean)
    }

    return JsonResponse(results, json_dumps_params={"indent": 2})

@csrf_exempt  
def distance_comparasion(request):
    if request.method == "POST":
        try:
            # Parse the request JSON  
            data = json.loads(request.body)  
            id_pegawai = data.get("id_pegawai")  
            vektor_presensi = np.array(data.get("vektor_presensi"), dtype=np.float32)  

            if not id_pegawai or vektor_presensi is None:  
                return JsonResponse({"error": "id_pegawai dan vektor_presensi wajib diisi"}, status=400)  

            # Retrieve face embeddings from the database  
            embeddings_data = VektorPegawai.objects.filter(
                id_pegawai=id_pegawai, deleted_at__isnull=True).values_list("face_embeddings", flat=True)  

            if not embeddings_data:  
                return JsonResponse({"error": "Data face embeddings tidak ditemukan"}, status=404)  

            # Convert face embeddings from JSON string to numpy array  
            embeddings = [np.array(json.loads(embedding), dtype=np.float32) for embedding in embeddings_data]  
            num_dimensions = len(vektor_presensi)  

            # Initialize results dictionary  
            results = {  
                "id_pegawai": id_pegawai,  
                "distance_metrics": {},  
                "computation_times": {}  
            }  

            # Helper function untuk mengukur waktu eksekusi dalam milidetik (ms)
            def measure_time(func):
                start_time = time.perf_counter()
                result = func()
                elapsed_time = (time.perf_counter() - start_time) * 1000  # Konversi ke milidetik (ms)
                return result, round(elapsed_time, 3)  # Dibulatkan ke 3 desimal

            # 1. Euclidean Distance using Voyager  
            def calc_euclidean_voyager():
                index = Index(Space.Euclidean, num_dimensions=num_dimensions)  
                if embeddings:  
                    index.add_items(np.array(embeddings))  
                    k = len(embeddings)  
                    _, distances = index.query(vektor_presensi, k=k)  
                    return [float(d) for d in distances]

            results["distance_metrics"]["euclidean_distance_voyager"], results["computation_times"]["euclidean_distance_voyager"] = measure_time(calc_euclidean_voyager)

            # 2. Euclidean Distance using Scipy  
            def calc_euclidean_scipy():
                return [distance.euclidean(vektor_presensi, embedding) for embedding in embeddings]

            results["distance_metrics"]["euclidean_distance_scipy"], results["computation_times"]["euclidean_distance_scipy"] = measure_time(calc_euclidean_scipy)

            # 3. Manhattan Distance  
            def calc_manhattan():
                return [distance.cityblock(vektor_presensi, embedding) for embedding in embeddings]

            results["distance_metrics"]["manhattan"], results["computation_times"]["manhattan"] = measure_time(calc_manhattan)

            # 4. Chebyshev Distance  
            def calc_chebyshev():
                return [distance.chebyshev(vektor_presensi, embedding) for embedding in embeddings]

            results["distance_metrics"]["chebyshev"], results["computation_times"]["chebyshev"] = measure_time(calc_chebyshev)

            # 5. Bray-Curtis Distance  
            def calc_braycurtis():
                return [distance.braycurtis(vektor_presensi, embedding) for embedding in embeddings]

            results["distance_metrics"]["braycurtis"], results["computation_times"]["braycurtis"] = measure_time(calc_braycurtis)

            # 6. Canberra Distance  
            def calc_canberra():
                return [distance.canberra(vektor_presensi, embedding) for embedding in embeddings]

            results["distance_metrics"]["canberra"], results["computation_times"]["canberra"] = measure_time(calc_canberra)

            # 7. Cosine Similarity using Voyager  
            def calc_cosine_voyager():
                index = Index(Space.Cosine, num_dimensions=num_dimensions)  
                if embeddings:  
                    index.add_items(np.array(embeddings))  
                    k = len(embeddings)  
                    _, distances = index.query(vektor_presensi, k=k)  
                    return [float(d) for d in distances]

            results["distance_metrics"]["cosine_similarity_voyager"], results["computation_times"]["cosine_similarity_voyager"] = measure_time(calc_cosine_voyager)

            # 8. Cosine Similarity using Scipy  
            def calc_cosine_scipy():
                return [distance.cosine(vektor_presensi, embedding) for embedding in embeddings]

            results["distance_metrics"]["cosine_similarity_scipy"], results["computation_times"]["cosine_similarity_scipy"] = measure_time(calc_cosine_scipy)

            # Convert all float32 values to float before returning JSON response
            results["distance_metrics"]["euclidean_distance_voyager"] = [float(d) for d in results["distance_metrics"]["euclidean_distance_voyager"]]
            results["distance_metrics"]["euclidean_distance_scipy"] = [float(d) for d in results["distance_metrics"]["euclidean_distance_scipy"]]
            results["distance_metrics"]["manhattan"] = [float(d) for d in results["distance_metrics"]["manhattan"]]
            results["distance_metrics"]["chebyshev"] = [float(d) for d in results["distance_metrics"]["chebyshev"]]
            results["distance_metrics"]["braycurtis"] = [float(d) for d in results["distance_metrics"]["braycurtis"]]
            results["distance_metrics"]["canberra"] = [float(d) for d in results["distance_metrics"]["canberra"]]
            results["distance_metrics"]["cosine_similarity_voyager"] = [float(d) for d in results["distance_metrics"]["cosine_similarity_voyager"]]
            results["distance_metrics"]["cosine_similarity_scipy"] = [float(d) for d in results["distance_metrics"]["cosine_similarity_scipy"]]

            # Konversi computation times ke format yang lebih mudah dibaca (dalam detik)
            for key in results["computation_times"]:
                results["computation_times"][key] = round(results["computation_times"][key], 6)  # Membulatkan hingga 6 desimal

            # Urutkan computation times dari yang tercepat hingga terlama
            sorted_computation_times = dict(sorted(results["computation_times"].items(), key=lambda item: item[1]))

            # Tambahkan hasil yang sudah diurutkan ke dalam results
            results["sorted_computation_times"] = sorted_computation_times

            # Tentukan apakah wajah terverifikasi berdasarkan Euclidean distance  
            verifikasi = any(d < 1 for d in results["distance_metrics"].get("euclidean_distance_scipy", []))  
            pesan = "Wajah terverifikasi" if verifikasi else "Wajah tidak terverifikasi"  
            value = 1 if verifikasi else 0  

            results["pesan"] = pesan  
            results["value"] = value  

            return JsonResponse(results)  

        except Exception as e:  
            return JsonResponse({"error": str(e)}, status=500)  

    return JsonResponse({"error": "Metode tidak diizinkan"}, status=405)

def check_progress(request, task_id):
    progress = get_progress(task_id)
    if progress:
        return JsonResponse(progress)
    else:
        return JsonResponse({"done": 0, "total": 0}, status=404)