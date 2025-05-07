import os
import re
import datetime
import numpy as np
import cv2
from io import BytesIO
from django.conf import settings
from google.oauth2 import service_account
from googleapiclient.discovery import build
from PIL import Image
import tensorflow as tf
from mtcnn import MTCNN

# Path ke kredensial Google Drive API
GOOGLE_CREDENTIALS_FILE = os.path.join(settings.BASE_DIR, 'assets/auth/credentials.json')

# Fungsi autentikasi ke Google Drive API
def get_drive_service():
    credentials = service_account.Credentials.from_service_account_file(
        GOOGLE_CREDENTIALS_FILE,
        scopes=["https://www.googleapis.com/auth/drive"]
    )
    return build("drive", "v3", credentials=credentials)

# Fungsi untuk memuat model MobileFaceNet (TFLite)
def load_tflite_model():
    model_path = os.path.join(settings.BASE_DIR, "assets/model/mobilefacenet.tflite")
    interpreter = tf.lite.Interpreter(model_path=model_path)
    interpreter.allocate_tensors()
    return interpreter

# Fungsi ekstraksi vektor wajah dari Google Drive tanpa menyimpan file lokal
def face_extraction(folder_id, id_pegawai):
    try:
        service = get_drive_service()
        interpreter = load_tflite_model()
        detector = MTCNN()

        # Buat folder untuk menyimpan hasil cropping
        save_dir = f"assets/images/pegawai/{id_pegawai}/"
        os.makedirs(save_dir, exist_ok=True)

        # Dapatkan file gambar dari folder Google Drive
        query = f"'{folder_id}' in parents and mimeType contains 'image/'"
        results = service.files().list(q=query, fields="files(id, name)").execute()
        files = results.get("files", [])

        if not files:
            print(f"Tidak ada gambar ditemukan di folder {folder_id}")
            return None

        vectors = []
        for i, file in enumerate(files):
            file_id = file["id"]
            file_name = file["name"]

            # Ambil gambar dari Google Drive tanpa menyimpannya
            request = service.files().get_media(fileId=file_id)
            image_data = BytesIO(request.execute())

            try:
                img = Image.open(image_data)
                img = img.convert("RGB")
                img_array = np.array(img)

                # Deteksi wajah dengan MTCNN
                faces = detector.detect_faces(img_array)
                if not faces:
                    print(f"Tidak ada wajah terdeteksi di {file_name}")
                    continue

                # Ambil bounding box wajah pertama
                x, y, width, height = faces[0]['box']
                face_crop = img_array[y:y+height, x:x+width]

                # Resize ke 112x112 (sesuai dengan input model MobileFaceNet)
                face_crop_resized = cv2.resize(face_crop, (112, 112))
                face_crop_resized = face_crop_resized.astype(np.float32) / 255.0  # Normalisasi
                face_crop_resized = np.expand_dims(face_crop_resized, axis=0)  # Tambahkan batch dimension

                # Ekstraksi fitur wajah dengan MobileFaceNet
                input_details = interpreter.get_input_details()
                output_details = interpreter.get_output_details()
                interpreter.set_tensor(input_details[0]['index'], face_crop_resized)
                interpreter.invoke()
                vector = interpreter.get_tensor(output_details[0]['index'])[0]  # Hasil vektor wajah
                vector = vector.tolist()

                # Simpan vektor hasil ekstraksi
                vectors.append(vector)  # Konversi ke list agar mudah disimpan

                print(f"Vektor wajah berhasil diekstrak dan disimpan untuk {file_name}")
                
                # Simpan gambar hasil crop secara lokal
                cropped_image_path = os.path.join(save_dir, f"face_{i+1}.jpg")
                Image.fromarray(face_crop).save(cropped_image_path)

            except Exception as img_err:
                print(f"Kesalahan dalam memproses gambar {file_name}: {img_err}")

        return vectors
    except Exception as e:
        print(f"Error dalam face_extraction: {e}")
        return None

# Extract folder ID from Google Drive folder URL
def extract_folder_id(url):
    # Pola regex untuk menangkap folder ID
    pattern = r"folders/([a-zA-Z0-9_-]+)"
    match = re.search(pattern, url)
    return match.group(1) if match else None