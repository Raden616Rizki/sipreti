<!doctype html>
<html>
    <head>
        <title>Detail Log Absensi</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Detail Log Absensi</h2>
        <table class="table">
	    <tr><td>Id Pegawai</td><td><?php echo $id_pegawai; ?></td></tr>
	    <tr><td>Jenis Absensi</td><td><?php echo $jenis_absensi; ?></td></tr>
	    <tr><td>Check Mode</td><td><?php echo $check_mode; ?></td></tr>
	    <tr><td>Waktu Absensi</td><td><?php echo $waktu_absensi; ?></td></tr>
	    <tr><td>Lattitude</td><td><?php echo $lattitude; ?></td></tr>
	    <tr><td>Longitude</td><td><?php echo $longitude; ?></td></tr>
	    <tr><td>Nama Lokasi</td><td><?php echo $nama_lokasi; ?></td></tr>
	    <tr><td>Url Foto Presensi</td><td><?php echo $url_foto_presensi; ?></td></tr>
	    <tr><td>Url Dokumen</td><td><?php echo $url_dokumen; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('log_absensi') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>
