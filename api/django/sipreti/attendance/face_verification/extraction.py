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

# Fungsi untuk memuat model MobileFaceNet (TFLite)
def load_tflite_model():
    model_path = os.path.join(settings.BASE_DIR, "assets/model/mobilefacenet.tflite")
    interpreter = tf.lite.Interpreter(model_path=model_path)
    interpreter.allocate_tensors()
    return interpreter

def load_tflite_facenet_model():
    model_path = os.path.join(settings.BASE_DIR, "assets/model/facenet.tflite")
    interpreter = tf.lite.Interpreter(model_path=model_path)
    interpreter.allocate_tensors()
    return interpreter

# Fungsi ekstraksi vektor wajah dari Google Drive tanpa menyimpan file lokal
def face_extraction_gdrive(folder_id, id_pegawai):
    try:
        service = get_drive_service()
        interpreter = load_tflite_model()
        detector = MTCNN()

        # Ambil daftar file gambar dari Google Drive
        query = f"'{folder_id}' in parents and mimeType contains 'image/'"
        results = service.files().list(q=query, fields="files(id, name)").execute()
        files = results.get("files", [])

        if not files:
            print(f"Tidak ada gambar yang ditemukan di folder {folder_id}")
            return None, None

        vectors = []
        original_images = []

        for i, file in enumerate(files):
            file_id = file["id"]
            file_name = file["name"]

            # Unduh gambar dari Google Drive
            request = service.files().get_media(fileId=file_id)
            image_data = BytesIO(request.execute())

            try:
                img = Image.open(image_data)
                img = img.convert("RGB")
                img_array = np.array(img)

                # Deteksi wajah menggunakan MTCNN
                faces = detector.detect_faces(img_array)
                if not faces:
                    print(f"Tidak ada wajah terdeteksi di {file_name}")
                    continue

                x, y, width, height = faces[0]['box']
                x = max(x, 0)
                y = max(y, 0)
        
                face_crop = img_array[y:y+height, x:x+width]

                # Resize dan normalisasi wajah
                face_crop_resized = cv2.resize(face_crop, (112, 112))
                face_crop_resized = face_crop_resized.astype(np.float32) / 255.0
                face_crop_resized = np.expand_dims(face_crop_resized, axis=0)

                # Ekstraksi embedding wajah menggunakan TFLite
                input_details = interpreter.get_input_details()
                output_details = interpreter.get_output_details()
                interpreter.set_tensor(input_details[0]['index'], face_crop_resized)
                interpreter.invoke()
                vector = interpreter.get_tensor(output_details[0]['index'])[0]
                vector = vector.tolist()

                # Simpan gambar asli sebagai file-like object
                original_io = BytesIO()
                original_format = img.format or 'JPEG'
                extension = original_format.lower()
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                original_io.name = f"{timestamp}_{id_pegawai}_original_{i+1}.{extension}"
                img.save(original_io, format=original_format)
                original_io.seek(0)
                
                crop_io = BytesIO()
                crop_image = Image.fromarray(face_crop)
                crop_io.name = f"{timestamp}_{id_pegawai}_crop.jpg"
                crop_image.save(crop_io, format='JPEG')
                crop_io.seek(0)

                vectors.append(vector)
                original_images.append(crop_io)

                print(f"Vektor wajah berhasil diekstrak dari {crop_io.name}")

            except Exception as img_err:
                print(f"Kesalahan dalam memproses gambar {file_name}: {img_err}")

        return vectors, original_images

    except Exception as e:
        print(f"Error dalam face_extraction: {e}")
        return None, None
    
def face_extraction_gdrive_facenet(folder_id, id_pegawai):
    try:
        service = get_drive_service()
        interpreter = load_tflite_facenet_model()
        detector = MTCNN()

        query = f"'{folder_id}' in parents and mimeType contains 'image/'"
        results = service.files().list(q=query, fields="files(id, name)").execute()
        files = results.get("files", [])

        if not files:
            print(f"Tidak ada gambar ditemukan di folder {folder_id}")
            return None, None

        vectors = []
        original_images = []

        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()

        # Ambil info kuantisasi untuk konversi ke float
        output_scale, output_zero_point = output_details[0]['quantization']

        for i, file in enumerate(files):
            file_id = file["id"]
            file_name = file["name"]

            try:
                request = service.files().get_media(fileId=file_id)
                image_data = BytesIO(request.execute())

                img = Image.open(image_data).convert("RGB")
                img_array = np.array(img)

                # Deteksi wajah
                faces = detector.detect_faces(img_array)
                if not faces:
                    print(f"Tidak ada wajah di {file_name}")
                    continue

                x, y, w, h = faces[0]['box']
                x = max(x, 0)
                y = max(y, 0)
                face_crop = img_array[y:y+h, x:x+w]

                # Resize ke 160x160 (sesuai model)
                face_crop_resized = cv2.resize(face_crop, (160, 160))
                face_input = face_crop_resized.astype(np.uint8)
                face_input = np.expand_dims(face_input, axis=0)

                # Inference
                interpreter.set_tensor(input_details[0]['index'], face_input)
                interpreter.invoke()
                output_data = interpreter.get_tensor(output_details[0]['index'])[0]

                # Konversi ke float32
                vector = [(val - output_zero_point) * output_scale for val in output_data]

                # Simpan gambar asli & crop
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                original_io = BytesIO()
                original_format = img.format or 'JPEG'
                ext = original_format.lower()
                original_io.name = f"{timestamp}_{id_pegawai}_original_{i+1}.{ext}"
                img.save(original_io, format=original_format)
                original_io.seek(0)

                crop_io = BytesIO()
                crop_img = Image.fromarray(face_crop)
                crop_io.name = f"{timestamp}_{id_pegawai}_crop_{i+1}.jpg"
                crop_img.save(crop_io, format='JPEG')
                crop_io.seek(0)

                vectors.append(vector)
                original_images.append(crop_io)
                print(f"Vektor wajah diekstrak dari {crop_io.name}")

            except Exception as img_err:
                print(f"Kesalahan gambar {file_name}: {img_err}")

        return vectors, original_images

    except Exception as e:
        print(f"Error utama di face_extraction_gdrive_facenet: {e}")
        return None, None
    
def face_extraction(uploaded_file, id_pegawai):
    try:
        interpreter = load_tflite_model()
        detector = MTCNN()

        image_data = BytesIO(uploaded_file.read())
        img = Image.open(image_data)
        img = img.convert("RGB")
        img_array = np.array(img)

        faces = detector.detect_faces(img_array)
        if not faces:
            print("Tidak ada wajah terdeteksi.")
            return None

        x, y, width, height = faces[0]['box']
        x = max(x, 0)
        y = max(y, 0)
        
        face_crop = img_array[y:y+height, x:x+width]

        face_crop_resized = cv2.resize(face_crop, (112, 112))
        face_crop_resized = face_crop_resized.astype(np.float32) / 255.0
        face_crop_resized = np.expand_dims(face_crop_resized, axis=0)

        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()
        interpreter.set_tensor(input_details[0]['index'], face_crop_resized)
        interpreter.invoke()
        vector = interpreter.get_tensor(output_details[0]['index'])[0].tolist()

        # Simpan ulang file asli untuk pengiriman ke CI3
        original_io = BytesIO()
        original_format = img.format or 'JPEG'  # fallback jika tidak terbaca
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        extension = original_format.lower()
        original_io.name = f"{timestamp}_{id_pegawai}_original.{extension}"
        img.save(original_io, format=original_format)
        original_io.seek(0)
        
        crop_io = BytesIO()
        crop_image = Image.fromarray(face_crop)
        crop_io.name = f"{timestamp}_{id_pegawai}_crop.jpg"
        crop_image.save(crop_io, format='JPEG')
        crop_io.seek(0)

        return vector, crop_io

    except Exception as e:
        print(f"Kesalahan saat ekstraksi wajah tunggal: {e}")
        return None
    
def face_extraction_facenet(uploaded_file, id_pegawai):
    try:
        interpreter = load_tflite_facenet_model()
        detector = MTCNN()

        # Buka gambar dari upload
        image_data = BytesIO(uploaded_file.read())
        img = Image.open(image_data).convert("RGB")
        img_array = np.array(img)

        # Deteksi wajah pertama
        faces = detector.detect_faces(img_array)
        if not faces:
            print("Tidak ada wajah terdeteksi.")
            return None

        x, y, width, height = faces[0]['box']
        x = max(x, 0)
        y = max(y, 0)
        face_crop = img_array[y:y+height, x:x+width]

        # Resize ke 160x160 seperti diminta model
        face_crop_resized = cv2.resize(face_crop, (160, 160))

        # Konversi ke uint8 (model expects uint8)
        face_input = face_crop_resized.astype(np.uint8)
        face_input = np.expand_dims(face_input, axis=0)

        # Inference dengan TFLite
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()

        interpreter.set_tensor(input_details[0]['index'], face_input)
        interpreter.invoke()

        output_data = interpreter.get_tensor(output_details[0]['index'])[0]

        # Output dalam bentuk uint8, konversi ke float32 jika ingin
        scale = output_details[0]['quantization'][0]
        zero_point = output_details[0]['quantization'][1]
        vector = [(val - zero_point) * scale for val in output_data]

        # Simpan file asli dan crop untuk CI3
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        original_io = BytesIO()
        original_format = img.format or 'JPEG'
        original_io.name = f"{timestamp}_{id_pegawai}_original.{original_format.lower()}"
        img.save(original_io, format=original_format)
        original_io.seek(0)

        crop_io = BytesIO()
        crop_image = Image.fromarray(face_crop)
        crop_io.name = f"{timestamp}_{id_pegawai}_crop.jpg"
        crop_image.save(crop_io, format='JPEG')
        crop_io.seek(0)

        return vector, crop_io

    except Exception as e:
        print(f"Kesalahan saat ekstraksi wajah (FaceNet): {e}")
        return None

# Extract folder ID from Google Drive folder URL
def extract_folder_id(url):
    # Pola regex untuk menangkap folder ID
    pattern = r"folders/([a-zA-Z0-9_-]+)"
    match = re.search(pattern, url)
    print(f'{url} success extracted!')
    return match.group(1) if match else None