import csv
import json
import numpy as np
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from .models import VektorPegawai
from .face_verification.extraction import face_extraction, extract_folder_id
from voyager import Index, Space

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