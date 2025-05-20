import csv
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
            raw_data = csv_file.read().decode("utf-8").splitlines()
            reader = csv.DictReader(raw_data)

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

                if not all([nip, nama, id_jabatan, id_unit_kerja, folder_url]):
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
                
                results = face_extraction_gdrive(folder_id, nip)
                if results:
                    vectors, originalImages = results
                    print(f"Berhasil mengekstrak {len(vectors)} wajah untuk NIP {nip}")

                    if vectors and originalImages:
                        try:
                            files_pegawai = {
                                'url_foto': originalImages[0]
                            }
                            data_pegawai = {
                                'nip': nip,
                                'nama': nama,
                                'id_jabatan': id_jabatan,
                                'id_unit_kerja': id_unit_kerja,
                            }

                            pegawai_response = requests.post(
                                settings.CI3_API_PEGAWAI_URL,
                                data=data_pegawai,
                                files=files_pegawai
                            )

                            if pegawai_response.status_code == 200:
                                print(f"Data pegawai berhasil dikirim: {nip}")
                                try:
                                    response_data = pegawai_response.json()
                                    id_pegawai = response_data.get("id_pegawai")

                                    if not id_pegawai:
                                        print(f"Gagal ambil id_pegawai dari response untuk NIP {nip}")
                                        current += 1
                                        if task_id:
                                            set_progress(task_id, current, total_rows)
                                        continue
                                except Exception as parse_err:
                                    print(f"Error parsing response JSON dari CI3: {parse_err}")
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

                            else:
                                print(f"CI3 Gagal (pegawai): {pegawai_response.status_code} - {pegawai_response.text}")

                        except Exception as e:
                            print(f"Gagal mengirim data pegawai {nip}: {e}")
                    else:
                        print(f"Tidak ada gambar untuk NIP {nip}")
                else:
                    print(f"Gagal ekstraksi wajah dari folder {folder_url} untuk NIP {nip}")

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