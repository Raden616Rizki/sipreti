from rest_framework import serializers
from .models import VektorPegawai

class CSVUploadSerializer(serializers.Serializer):
    csv_file = serializers.FileField()

class VektorPegawaiSerializer(serializers.ModelSerializer):
    class Meta:
        model = VektorPegawai
        fields = '__all__'
