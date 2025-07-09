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
    # model_path = os.path.join(settings.BASE_DIR, "assets/model/ghostfacenet.tflite")
    model_path = os.path.join(settings.BASE_DIR, "assets/model/mobilenetv2.tflite")
    interpreter = tf.lite.Interpreter(model_path=model_path)
    interpreter.allocate_tensors()
    return interpreter

# Fungsi ekstraksi vektor wajah dari Google Drive tanpa menyimpan file lokal
def face_extraction_gdrive_ghostfacenet(folder_id, id_pegawai):
    try:
        # Inisialisasi service dan model
        service = get_drive_service()
        interpreter = load_tflite_model()
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()
        detector = MTCNN()

        # Ambil file gambar dari folder Google Drive
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

                # Deteksi wajah
                faces = detector.detect_faces(img_array)
                if not faces:
                    print(f"Tidak ada wajah terdeteksi di {file_name}")
                    continue

                # Ambil wajah pertama
                x, y, width, height = faces[0]['box']
                x, y = max(0, x), max(0, y)
                face_crop = img_array[y:y+height, x:x+width]

                # Resize ke 112x112 dan normalisasi ke [-1, 1]
                face_resized = cv2.resize(face_crop, (112, 112))
                face_normalized = (face_resized.astype(np.float32) - 127.5) / 127.5
                input_tensor = np.expand_dims(face_normalized, axis=0)

                # Inference
                interpreter.set_tensor(input_details[0]['index'], input_tensor)
                interpreter.invoke()
                embedding = interpreter.get_tensor(output_details[0]['index'])[0]

                # Simpan wajah hasil crop
                crop_io = BytesIO()
                crop_image = Image.fromarray(face_crop)
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                crop_io.name = f"{timestamp}_{id_pegawai}_crop_{i+1}.jpg"
                crop_image.save(crop_io, format='JPEG')
                crop_io.seek(0)

                vectors.append(embedding.tolist())
                cropped_images.append(crop_io)

                print(f"Vektor wajah berhasil diekstrak dari {file_name}")

            except Exception as img_err:
                print(f"Gagal memproses gambar {file_name}: {img_err}")

        return vectors, cropped_images

    except Exception as e:
        print(f"Error dalam face_extraction_gdrive_ghostfacenet: {e}")
        return None, None

def face_extraction_ghostfacenet(uploaded_file, id_pegawai):
    try:
        # Load TFLite model
        interpreter = load_tflite_model()
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()

        # Baca dan konversi gambar
        image_data = BytesIO(uploaded_file.read())
        img = Image.open(image_data).convert("RGB")
        img_array = np.array(img)

        # Deteksi wajah dengan MTCNN
        detector = MTCNN()
        faces = detector.detect_faces(img_array)

        if not faces:
            print("Tidak ada wajah terdeteksi.")
            return None, None

        # Ambil wajah pertama yang terdeteksi
        x, y, width, height = faces[0]['box']
        x, y = max(0, x), max(0, y)
        face_crop = img_array[y:y+height, x:x+width]

        # Resize ke 112x112 dan normalisasi ke [-1, 1]
        face_resized = cv2.resize(face_crop, (112, 112))
        face_normalized = (face_resized.astype(np.float32) - 127.5) / 127.5
        input_tensor = np.expand_dims(face_normalized, axis=0)

        # Jalankan GhostFaceNet TFLite untuk mendapatkan embedding
        interpreter.set_tensor(input_details[0]['index'], input_tensor)
        interpreter.invoke()
        embedding = interpreter.get_tensor(output_details[0]['index'])[0].tolist()

        # Simpan ulang gambar asli (jika dibutuhkan)
        original_io = BytesIO()
        original_format = img.format or 'JPEG'
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        extension = original_format.lower()
        original_io.name = f"{timestamp}_{id_pegawai}_original.{extension}"
        img.save(original_io, format=original_format)
        original_io.seek(0)

        # Simpan wajah hasil crop
        crop_io = BytesIO()
        crop_image = Image.fromarray(face_crop)
        crop_io.name = f"{timestamp}_{id_pegawai}_crop.jpg"
        crop_image.save(crop_io, format='JPEG')
        crop_io.seek(0)

        return embedding, crop_io

    except Exception as e:
        print(f"Kesalahan saat ekstraksi wajah: {e}")
        return None, None
    
def extract_cropped_face_ghostfacenet(cropped_file, id_pegawai):
    try:
        # Load TFLite GhostFaceNet interpreter
        interpreter = load_tflite_model()
        input_details = interpreter.get_input_details()
        output_details = interpreter.get_output_details()

        # Baca file wajah yang sudah ter-crop
        image_data = BytesIO(cropped_file.read())
        img = Image.open(image_data).convert("RGB")
        img = img.resize((112, 112))  # Ukuran input FaceNet

        # Preprocessing: ubah ke np.array dan normalisasi ke [-1, 1]
        img_array = np.asarray(img).astype('float32')
        normalized = (img_array - 127.5) / 127.5
        input_tensor = np.expand_dims(normalized, axis=0)

        # Set input ke interpreter dan jalankan
        interpreter.set_tensor(input_details[0]['index'], input_tensor)
        interpreter.invoke()
        embedding = interpreter.get_tensor(output_details[0]['index'])[0]

        # Simpan ulang wajah crop jika perlu
        processed_io = BytesIO()
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        processed_io.name = f"{timestamp}_{id_pegawai}_processed.jpg"
        img.save(processed_io, format='JPEG')
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