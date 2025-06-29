from django.urls import path
from .views import upload_csv, face_register, face_verification, distance_comparasion, check_progress, upload_csv_pegawai, evaluate_face_recognition, evaluate_roc_curve, evaluate_face_recognition_api, upload_csv_pegawai_facenet, face_register_facenet

urlpatterns = [
    path('upload-csv/', upload_csv, name='upload_csv'),
    path('upload-csv-pegawai/', upload_csv_pegawai, name='upload_csv_pegawai'),
    path('upload-csv-pegawai-facenet/', upload_csv_pegawai_facenet, name='upload_csv_pegawai_facenet'),
    path('face-register/', face_register, name='face_register'),
    path('face-register-facenet/', face_register_facenet, name='face_register_facenet'),
    path('face-verification/', face_verification, name="face_verification"),
    path('distance-comparasion/', distance_comparasion, name="distance_comparasion"),
    path('evaluate-face-recognition/', evaluate_face_recognition, name="evaluate_face_recognition"),
    path('evaluate-face-recognition-api/', evaluate_face_recognition_api, name="evaluate_face_recognition_api"),
    path('evaluate-roc-curve/', evaluate_roc_curve, name="evaluate_roc_curve"),
    path('progress/<str:task_id>/', check_progress, name='check_progress'),
]
