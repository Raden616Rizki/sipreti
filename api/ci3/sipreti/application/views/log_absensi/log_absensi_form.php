<!doctype html>
<html>
    <head>
        <title>harviacode.com - codeigniter crud generator</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Log_absensi <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="int">Id Pegawai <?php echo form_error('id_pegawai') ?></label>
            <input type="text" class="form-control" name="id_pegawai" id="id_pegawai" placeholder="Id Pegawai" value="<?php echo $id_pegawai; ?>" />
        </div>
	    <div class="form-group">
            <label for="tinyint">Jenis Absensi <?php echo form_error('jenis_absensi') ?></label>
            <input type="text" class="form-control" name="jenis_absensi" id="jenis_absensi" placeholder="Jenis Absensi" value="<?php echo $jenis_absensi; ?>" />
        </div>
	    <div class="form-group">
            <label for="tinyint">Check Mode <?php echo form_error('check_mode') ?></label>
            <input type="text" class="form-control" name="check_mode" id="check_mode" placeholder="Check Mode" value="<?php echo $check_mode; ?>" />
        </div>
	    <div class="form-group">
            <label for="datetime">Waktu Absensi <?php echo form_error('waktu_absensi') ?></label>
            <input type="text" class="form-control" name="waktu_absensi" id="waktu_absensi" placeholder="Waktu Absensi" value="<?php echo $waktu_absensi; ?>" />
        </div>
	    <div class="form-group">
            <label for="double">Lattitude <?php echo form_error('lattitude') ?></label>
            <input type="text" class="form-control" name="lattitude" id="lattitude" placeholder="Lattitude" value="<?php echo $lattitude; ?>" />
        </div>
	    <div class="form-group">
            <label for="double">Longitude <?php echo form_error('longitude') ?></label>
            <input type="text" class="form-control" name="longitude" id="longitude" placeholder="Longitude" value="<?php echo $longitude; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Nama Lokasi <?php echo form_error('nama_lokasi') ?></label>
            <input type="text" class="form-control" name="nama_lokasi" id="nama_lokasi" placeholder="Nama Lokasi" value="<?php echo $nama_lokasi; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Nama Kamera <?php echo form_error('nama_kamera') ?></label>
            <input type="text" class="form-control" name="nama_kamera" id="nama_kamera" placeholder="Nama Kamera" value="<?php echo $nama_kamera; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Url Foto Presensi <?php echo form_error('url_foto_presensi') ?></label>
            <input type="text" class="form-control" name="url_foto_presensi" id="url_foto_presensi" placeholder="Url Foto Presensi" value="<?php echo $url_foto_presensi; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Url Dokumen <?php echo form_error('url_dokumen') ?></label>
            <input type="text" class="form-control" name="url_dokumen" id="url_dokumen" placeholder="Url Dokumen" value="<?php echo $url_dokumen; ?>" />
        </div>
	    <input type="hidden" name="id_log_absensi" value="<?php echo $id_log_absensi; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('log_absensi') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>
