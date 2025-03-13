import os
import re
# import cv2
# import numpy as np
import requests
import datetime
# import tensorflow as tf
import pandas as pd
from PIL import Image
from io import BytesIO
from django.conf import settings
from google.oauth2 import service_account
from googleapiclient.discovery import build

# Path penyimpanan gambar
GOOGLE_CREDENTIALS_FILE = os.path.join(settings.BASE_DIR, 'assets/auth/credentials.json')
ASSETS_DIR = os.path.join(settings.BASE_DIR, 'assets/images/pegawai')
os.makedirs(ASSETS_DIR, exist_ok=True)

# Authenticate with Google Drive API
def get_drive_service():
    credentials = service_account.Credentials.from_service_account_file(
        GOOGLE_CREDENTIALS_FILE,
        scopes=["https://www.googleapis.com/auth/drive"]
    )
    return build("drive", "v3", credentials=credentials)

# Download images from a Google Drive folder
def download_images(folder_id, id_pegawai):
    try:
        service = get_drive_service()

        # Get files from the folder
        query = f"'{folder_id}' in parents and mimeType contains 'image/'"
        results = service.files().list(q=query, fields="files(id, name)").execute()
        files = results.get("files", [])

        if not files:
            print(f"No images found in folder {folder_id}")
            return None

        saved_files = []
        for file in files:
            file_id = file["id"]
            file_name = file["name"]

            # Download the image
            request = service.files().get_media(fileId=file_id)
            image_data = BytesIO(request.execute())

            # Buat sub-folder berdasarkan id_pegawai
            pegawai_folder = os.path.join(ASSETS_DIR, id_pegawai)
            os.makedirs(pegawai_folder, exist_ok=True)

            # Tambahkan timestamp ke nama file (format: YYYYMMDD_HHMMSS)
            timestamp = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")

            # Buat nama file unik dengan format id_pegawai_file_name_date_time
            unique_filename = f"{id_pegawai}_{timestamp}_{file_name}"
            file_path = os.path.join(pegawai_folder, unique_filename)
            
            # Ensure valid image format
            try:
                img = Image.open(image_data)
                img.save(file_path)
                saved_files.append(file_path)
                print(f"Saved image: {file_path}")
            except Exception as img_err:
                print(f"Invalid image format: {file_name}, Error: {img_err}")

        return saved_files
    except Exception as e:
        print(f"Error downloading images: {e}")
        return None

# Extract folder ID from Google Drive folder URL
def extract_folder_id(url):
    # Pola regex untuk menangkap folder ID
    pattern = r"folders/([a-zA-Z0-9_-]+)"
    match = re.search(pattern, url)
    return match.group(1) if match else None
