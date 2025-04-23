import csv
import json
import numpy as np
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from .models import VektorPegawai
from .face_verification.extraction import face_extraction, extract_folder_id
from voyager import Index, Space
import time
from scipy.spatial import distance

@csrf_exempt
def upload_csv(request):
    if request.method == "POST" and request.FILES.get("file"):
        csv_file = request.FILES["file"]
        decoded_file = csv_file.read().decode("utf-8").splitlines()
        reader = csv.DictReader(decoded_file)

        for row in reader:
            id_pegawai = row.get("id_pegawai")
            folder_url = row.get("url_photo_folder")

            if not id_pegawai or not folder_url:
                continue

            # Ekstraksi folder ID dari Google Drive URL
            folder_id = extract_folder_id(folder_url)
            if not folder_id:
                print(f"URL folder tidak valid: {folder_url}")
                continue

            # Ekstraksi vektor wajah
            vectors = face_extraction(folder_id, id_pegawai)
            if vectors:
                print(f"Berhasil mengekstrak {len(vectors)} vektor wajah untuk ID pegawai {id_pegawai}")

                # Simpan ke database
                for vector in vectors:
                    VektorPegawai.objects.create(
                        id_pegawai=id_pegawai,
                        face_embeddings=json.dumps(vector),  # Simpan sebagai JSON string
                        url_foto="-"
                    )
                print(f"Vektor wajah berhasil disimpan di database untuk ID pegawai {id_pegawai}")

            else:
                print(f"Tidak ada vektor wajah yang diekstrak untuk ID pegawai {id_pegawai}")
                return JsonResponse({'message': 'Data tidak berhasil diproses'}, status=500)

        return JsonResponse({'message': 'Data berhasil diproses'}, status=200)

    return JsonResponse({'error': 'Invalid request'}, status=400)

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
            num_dimensions = len(vektor_presensi)

            # Buat index Voyager
            index = Index(Space.Euclidean, num_dimensions=num_dimensions)
            if embeddings:
                index.add_items(np.array(embeddings))

            # Query dengan vektor_presensi
            k = len(embeddings)
            neighbors, distances = index.query(vektor_presensi, k=k)
            distances = [float(d) for d in distances] 
            
            # Cek apakah ada jarak di bawah 1 (threshold)
            verifikasi = any(d < 1 for d in distances)
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
