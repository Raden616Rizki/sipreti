import csv
import json
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from .models import VektorPegawai
from .face_verification.extraction import face_extraction, extract_folder_id

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
