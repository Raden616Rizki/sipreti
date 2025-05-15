from django.urls import path
from .views import upload_csv, face_register, face_verification, distance_comparasion

urlpatterns = [
    path('upload-csv/', upload_csv, name='upload_csv'),
    path('face-register/', face_register, name='face_register'),
    path('face-verification/', face_verification, name="face_verification"),
    path('distance-comparasion/', distance_comparasion, name="distance_comparasion"),
]
