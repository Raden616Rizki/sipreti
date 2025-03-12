import os
import cv2
import numpy as np
import requests
import tensorflow as tf
import pandas as pd
from PIL import Image
from io import BytesIO
from django.conf import settings

# Path penyimpanan gambar
ASSETS_DIR = os.path.join(settings.BASE_DIR, "assets")
os.makedirs(ASSETS_DIR, exist_ok=True)

# Load model MobileFaceNet.tflite
MODEL_PATH = os.path.join(settings.BASE_DIR, "model", "mobilefacenet.tflite")
interpreter = tf.lite.Interpreter(model_path=MODEL_PATH)
interpreter.allocate_tensors()

input_details = interpreter.get_input_details()
output_details = interpreter.get_output_details()

# Fungsi untuk mengunduh dan menyimpan gambar dari URL
def download_images_from_gdrive(folder_url):
    try:
        # Google Drive file format: https://drive.google.com/uc?id=FILE_ID
        folder_id = folder_url.split("/")[-1]
        response = requests.get(f"https://drive.google.com/uc?id={folder_id}")
        if response.status_code == 200:
            img = Image.open(BytesIO(response.content))
            filename = f"{folder_id}.jpg"
            file_path = os.path.join(ASSETS_DIR, filename)
            img.save(file_path)
            return file_path
        else:
            return None
    except Exception as e:
        print(f"Error downloading image: {e}")
        return None

# Fungsi untuk mengekstrak fitur wajah menggunakan MobileFaceNet
def extract_face_embedding(image_path):
    try:
        img = cv2.imread(image_path)
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        img = cv2.resize(img, (112, 112))
        img = np.expand_dims(img, axis=0).astype(np.float32)

        interpreter.set_tensor(input_details[0]['index'], img)
        interpreter.invoke()

        embedding = interpreter.get_tensor(output_details[0]['index'])
        return embedding.flatten().tolist()
    except Exception as e:
        print(f"Error extracting face embedding: {e}")
        return None
