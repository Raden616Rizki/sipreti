from django.urls import path
from .views import upload_csv, face_verification

urlpatterns = [
    path('upload-csv/', upload_csv, name='upload_csv'),
    path('face-verification/', face_verification, name="face_verification"),
]
