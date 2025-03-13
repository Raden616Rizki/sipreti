import os
import csv
import requests
import numpy as np
import cv2
# import tensorflow as tf
from django.conf import settings
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
# from mtcnn import MTCNN
from .models import VektorPegawai
from .utils import download_images, extract_folder_id

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

            # Download gambar
            image_paths = download_images(folder_id, id_pegawai)
            if image_paths:
                print(f"Berhasil mengunduh {len(image_paths)} gambar untuk ID pegawai {id_pegawai}")
            else:
                print(f"Tidak ada gambar yang diunduh untuk ID pegawai {id_pegawai}")


        return JsonResponse({'message': 'Data berhasil diproses'}, status=200)

    return JsonResponse({'error': 'Invalid request'}, status=400)