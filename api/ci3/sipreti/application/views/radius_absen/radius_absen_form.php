<!doctype html>
<html>
    <head>
        <title>Kelola Radius Absen</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Radius Absen <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="float">Ukuran <?php echo form_error('ukuran') ?></label>
            <input type="text" class="form-control" name="ukuran" id="ukuran" placeholder="Ukuran" value="<?php echo $ukuran; ?>" />
        </div>
	    <input type="hidden" name="id_radius" value="<?php echo $id_radius; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('radius_absen') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>
