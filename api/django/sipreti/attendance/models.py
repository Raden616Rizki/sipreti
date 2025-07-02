from django.db import models

class VektorPegawai(models.Model):
    id_vektor_pegawai = models.AutoField(primary_key=True)
    id_pegawai = models.IntegerField()
    face_embeddings = models.TextField()
    url_foto = models.CharField(max_length=255, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)
    
    class Meta:
        db_table = "vektor_pegawai"
        
class VektorPegawaiFacenet(models.Model):
    id_vektor_pegawai = models.AutoField(primary_key=True)
    id_pegawai = models.IntegerField()
    face_embeddings = models.TextField()
    url_foto = models.CharField(max_length=255, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)
    
    class Meta:
        db_table = "vektor_pegawai_facenet"
        
class VektorPegawaiGhostfacenet(models.Model):
    id_vektor_pegawai = models.AutoField(primary_key=True)
    id_pegawai = models.IntegerField()
    face_embeddings = models.TextField()
    url_foto = models.CharField(max_length=255, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    deleted_at = models.DateTimeField(null=True, blank=True)
    
    class Meta:
        db_table = "vektor_pegawai_ghostfacenet"