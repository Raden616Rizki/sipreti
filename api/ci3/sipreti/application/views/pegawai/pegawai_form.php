<!doctype html>
<html>
    <head>
        <title>Kelola Pegawai</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Pegawai <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="int">Id Jabatan <?php echo form_error('id_jabatan') ?></label>
            <input type="text" class="form-control" name="id_jabatan" id="id_jabatan" placeholder="Id Jabatan" value="<?php echo $id_jabatan; ?>" />
        </div>
	    <div class="form-group">
            <label for="int">Id Unit Kerja <?php echo form_error('id_unit_kerja') ?></label>
            <input type="text" class="form-control" name="id_unit_kerja" id="id_unit_kerja" placeholder="Id Unit Kerja" value="<?php echo $id_unit_kerja; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Nip <?php echo form_error('nip') ?></label>
            <input type="text" class="form-control" name="nip" id="nip" placeholder="Nip" value="<?php echo $nip; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Nama <?php echo form_error('nama') ?></label>
            <input type="text" class="form-control" name="nama" id="nama" placeholder="Nama" value="<?php echo $nama; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Url Foto <?php echo form_error('url_foto') ?></label>
            <input type="text" class="form-control" name="url_foto" id="url_foto" placeholder="Url Foto" value="<?php echo $url_foto; ?>" />
        </div>
	    <input type="hidden" name="id_pegawai" value="<?php echo $id_pegawai; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('pegawai') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>
