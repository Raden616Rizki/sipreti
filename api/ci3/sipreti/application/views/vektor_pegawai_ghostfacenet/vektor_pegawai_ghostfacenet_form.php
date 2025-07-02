<!doctype html>
<html>
    <head>
        <title>Kelola Vektor Pegawai</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Vektor Pegawai <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="int">Id Pegawai <?php echo form_error('id_pegawai') ?></label>
            <input type="text" class="form-control" name="id_pegawai" id="id_pegawai" placeholder="Id Pegawai" value="<?php echo $id_pegawai; ?>" />
        </div>
	    <div class="form-group">
            <label for="face_embeddings">Face Embeddings <?php echo form_error('face_embeddings') ?></label>
            <textarea class="form-control" rows="3" name="face_embeddings" id="face_embeddings" placeholder="Face Embeddings"><?php echo $face_embeddings; ?></textarea>
        </div>
	    <div class="form-group">
            <label for="varchar">Url Foto <?php echo form_error('url_foto') ?></label>
            <input type="text" class="form-control" name="url_foto" id="url_foto" placeholder="Url Foto" value="<?php echo $url_foto; ?>" />
        </div>
	    <input type="hidden" name="id_vektor_pegawai" value="<?php echo $id_vektor_pegawai; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('vektor_pegawai') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>
