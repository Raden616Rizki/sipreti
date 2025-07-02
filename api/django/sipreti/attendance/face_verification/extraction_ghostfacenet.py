import os
import re
from datetime import datetime
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

# Fungsi untuk memuat model Ghostfacenet (TFLite)
def load_tflite_model():
    model_path = os.path.join(settings.BASE_DIR, "assets/model/ghostfacenet.tflite")
    interpreter = tf.lite.Interpreter(model_path=model_path)
    interpreter.allocate_tensors()
    return interpreter

def extract_face(image_np, detector=None, required_size=(112, 112)):
    if detector is None:
        detector = MTCNN()
    
    faces = detector.detect_faces(image_np)
    if len(faces) == 0:
        raise Exception("No face detected.")
    
    x, y, w, h = faces[0]['box']
    x, y = abs(x), abs(y)
    face = image_np[y:y+h, x:x+w]

    face_resized = Image.fromarray(face).resize(required_size)
    return np.asarray(face_resized), face  # Return both resized & raw crop

def prewhiten(img):
    mean, std = img.mean(), img.std()
    std_adj = np.maximum(std, 1.0 / np.sqrt(img.size))
    return (img - mean) / std_adj

def get_face_embedding(image_np, interpreter, input_details, output_details):
    face_resized, raw_crop = extract_face(image_np)
    face_norm = prewhiten(face_resized)
    face_norm = np.expand_dims(face_norm.astype(np.float32), axis=0)

    interpreter.set_tensor(input_details[0]['index'], face_norm)
    interpreter.invoke()
    embedding_raw = interpreter.get_tensor(output_details[0]['index'])
    embedding = embedding_raw[0] if isinstance(embedding_raw, (np.ndarray, list)) else embedding_raw

    return embedding, raw_crop

# Fungsi ekstraksi vektor wajah dari Google Drive tanpa menyimpan file lokal
def face_extraction_gdrive_ghostfacenet(folder_id, id_pegawai):
    try:
        service = get_drive_service()
        interpreter = load_tflite_model()
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()
        detector = MTCNN()

        query = f"'{folder_id}' in parents and mimeType contains 'image/'"
        results = service.files().list(q=query, fields="files(id, name)").execute()
        files = results.get("files", [])

        if not files:
            print(f"Tidak ada gambar yang ditemukan di folder {folder_id}")
            return None, None

        vectors = []
        cropped_images = []

        for i, file in enumerate(files):
            file_id = file["id"]
            file_name = file["name"]

            try:
                # Unduh gambar dari Google Drive
                request = service.files().get_media(fileId=file_id)
                image_data = BytesIO(request.execute())

                img = Image.open(image_data).convert("RGB")
                img_array = np.array(img)

                # Ekstrak embedding dan crop wajah
                try:
                    vector, raw_crop = get_face_embedding(
                        img_array, interpreter, input_details, output_details, detector
                    )
                except Exception as detect_err:
                    print(f"Tidak ada wajah terdeteksi di {file_name}: {detect_err}")
                    continue

                # Simpan wajah hasil crop ke BytesIO
                crop_io = BytesIO()
                crop_image = Image.fromarray(raw_crop)
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                crop_io.name = f"{timestamp}_{id_pegawai}_crop_{i+1}.jpg"
                crop_image.save(crop_io, format='JPEG')
                crop_io.seek(0)

                # Simpan original juga jika dibutuhkan
                # (opsional, jika hanya crop yang penting, bisa dihapus)
                # original_io = BytesIO()
                # img_format = img.format or 'JPEG'
                # original_io.name = f"{timestamp}_{id_pegawai}_original_{i+1}.{img_format.lower()}"
                # img.save(original_io, format=img_format)
                # original_io.seek(0)

                vectors.append(vector)
                cropped_images.append(crop_io)

                print(f"Vektor wajah berhasil diekstrak dari {file_name}")

            except Exception as img_err:
                print(f"Gagal memproses gambar {file_name}: {img_err}")

        return vectors, cropped_images

    except Exception as e:
        print(f"Error dalam face_extraction_gdrive: {e}")
        return None, None
    

def face_extraction_ghostfacenet(uploaded_file, id_pegawai):
    try:
        interpreter = load_tflite_model()
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()

        # Baca file gambar
        image_data = BytesIO(uploaded_file.read())
        img = Image.open(image_data).convert("RGB")
        img_array = np.array(img)

        # Ekstraksi embedding dan wajah crop
        vector, raw_crop = get_face_embedding(img_array, interpreter, input_details, output_details)

        # Simpan ulang file asli
        original_io = BytesIO()
        original_format = img.format or 'JPEG'
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        extension = original_format.lower()
        original_io.name = f"{timestamp}_{id_pegawai}_original.{extension}"
        img.save(original_io, format=original_format)
        original_io.seek(0)

        # Simpan wajah hasil crop
        crop_io = BytesIO()
        crop_image = Image.fromarray(raw_crop)
        crop_io.name = f"{timestamp}_{id_pegawai}_crop.jpg"
        crop_image.save(crop_io, format='JPEG')
        crop_io.seek(0)

        return vector, crop_io

    except Exception as e:
        print(f"Kesalahan saat ekstraksi wajah tunggal: {e}")
        return None
    
def extract_cropped_face_ghostfacenet(cropped_file, id_pegawai):
    try:
        # Load interpreter dan detail
        interpreter = load_tflite_model()
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()

        # Buka gambar dari file upload
        image_data = BytesIO(cropped_file.read())
        img = Image.open(image_data).convert("RGB")
        image_np = np.asarray(img)

        # Ekstraksi embedding & raw crop
        embedding, raw_crop = get_face_embedding(image_np, interpreter, input_details, output_details)

        # Simpan ulang wajah hasil crop ke memori
        processed_io = BytesIO()
        crop_img = Image.fromarray(raw_crop)
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        processed_io.name = f"{timestamp}_{id_pegawai}_processed.jpg"
        crop_img.save(processed_io, format='JPEG')
        processed_io.seek(0)

        return embedding, processed_io

    except Exception as e:
        print(f"Kesalahan saat ekstraksi embedding dari wajah crop: {e}")
        return None, None
    
# Extract folder ID from Google Drive folder URL
def extract_folder_id(url):
    # Pola regex untuk menangkap folder ID
    pattern = r"folders/([a-zA-Z0-9_-]+)"
    match = re.search(pattern, url)
    print(f'{url} success extracted!')
    return match.group(1) if match else None