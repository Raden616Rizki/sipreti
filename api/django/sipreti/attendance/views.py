import os
import csv
import requests
import numpy as np
import cv2
import tensorflow as tf
from django.conf import settings
from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
from mtcnn import MTCNN
from .models import VektorPegawai

# Load model MobileFaceNet (TFLite)
model_path = os.path.join(settings.BASE_DIR, 'assets/model/mobilefacenet.tflite')
interpreter = tf.lite.Interpreter(model_path=model_path)
interpreter.allocate_tensors()

# MTCNN untuk deteksi wajah
detector = MTCNN()

# Fungsi untuk mengekstrak fitur wajah menggunakan MobileFaceNet
def extract_face_embeddings(face_img):
    face_img = cv2.resize(face_img, (112, 112))
    face_img = face_img.astype(np.float32) / 255.0
    face_img = np.expand_dims(face_img, axis=0)

    input_details = interpreter.get_input_details()
    output_details = interpreter.get_output_details()

    interpreter.set_tensor(input_details[0]['index'], face_img)
    interpreter.invoke()
    embeddings = interpreter.get_tensor(output_details[0]['index'])

    return embeddings.flatten().tolist()

# Fungsi untuk mengambil dan menyimpan gambar dari Google Drive
import os
import requests
from django.conf import settings

def download_images_from_drive(folder_url, id_pegawai):
    response = requests.get(folder_url)
    if response.status_code != 200:
        return None

    # Buat folder assets/images/pegawai jika belum ada
    base_dir = os.path.join(settings.BASE_DIR, 'assets/images/pegawai')
    os.makedirs(base_dir, exist_ok=True)

    # Buat folder khusus untuk setiap pegawai berdasarkan id_pegawai
    pegawai_dir = os.path.join(base_dir, str(id_pegawai))
    os.makedirs(pegawai_dir, exist_ok=True)

    # Tentukan nomor urut file agar tidak overwrite
    existing_files = os.listdir(pegawai_dir)
    file_count = len([f for f in existing_files if f.endswith(".jpg")])
    file_name = f"{id_pegawai}_{file_count + 1}.jpg"

    file_path = os.path.join(pegawai_dir, file_name)

    # Simpan gambar ke dalam folder pegawai
    with open(file_path, 'wb') as f:
        f.write(response.content)

    return file_path

@csrf_exempt
def upload_csv(request):
    if request.method == 'POST' and request.FILES.get('file'):
        csv_file = request.FILES['file']
        decoded_file = csv_file.read().decode('utf-8').splitlines()
        reader = csv.DictReader(decoded_file)

        for row in reader:
            id_pegawai = row.get('id_pegawai')
            folder_url = row.get('url_photo_folder')

            if not id_pegawai or not folder_url:
                continue

            image_path = download_images_from_drive(folder_url, id_pegawai)
            if not image_path:
                continue

            img = cv2.imread(image_path)
            img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
            faces = detector.detect_faces(img_rgb)

            if not faces:
                continue

            x, y, width, height = faces[0]['box']
            face_img = img_rgb[y:y+height, x:x+width]

            face_embeddings = extract_face_embeddings(face_img)

            # Simpan data ke database
            VektorPegawai.objects.create(
                id_pegawai=id_pegawai,
                face_embeddings=str(face_embeddings),
                url_foto=image_path
            )

        return JsonResponse({'message': 'Data berhasil diproses'}, status=200)

    return JsonResponse({'error': 'Invalid request'}, status=400)