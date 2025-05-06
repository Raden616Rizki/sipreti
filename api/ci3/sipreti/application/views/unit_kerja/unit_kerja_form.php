<!doctype html>
<html>
    <head>
        <title>Kelola Unit Kerja</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Unit Kerja <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="int">Id Radius <?php echo form_error('id_radius') ?></label>
            <input type="text" class="form-control" name="id_radius" id="id_radius" placeholder="Id Radius" value="<?php echo $id_radius; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Nama Unit Kerja <?php echo form_error('nama_unit_kerja') ?></label>
            <input type="text" class="form-control" name="nama_unit_kerja" id="nama_unit_kerja" placeholder="Nama Unit Kerja" value="<?php echo $nama_unit_kerja; ?>" />
        </div>
	    <div class="form-group">
            <label for="alamat">Alamat <?php echo form_error('alamat') ?></label>
            <textarea class="form-control" rows="3" name="alamat" id="alamat" placeholder="Alamat"><?php echo $alamat; ?></textarea>
        </div>
	    <div class="form-group">
            <label for="double">Lattitude <?php echo form_error('lattitude') ?></label>
            <input type="text" class="form-control" name="lattitude" id="lattitude" placeholder="Lattitude" value="<?php echo $lattitude; ?>" />
        </div>
	    <div class="form-group">
            <label for="double">Longitude <?php echo form_error('longitude') ?></label>
            <input type="text" class="form-control" name="longitude" id="longitude" placeholder="Longitude" value="<?php echo $longitude; ?>" />
        </div>
	    <input type="hidden" name="id_unit_kerja" value="<?php echo $id_unit_kerja; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('unit_kerja') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>
