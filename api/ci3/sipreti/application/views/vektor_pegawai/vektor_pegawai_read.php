<!doctype html>
<html>
    <head>
        <title>Detail Vektor Pegawai</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Detail Vektor Pegawai</h2>
        <table class="table">
	    <tr><td>Id Pegawai</td><td><?php echo $id_pegawai; ?></td></tr>
	    <tr><td>Face Embeddings</td><td><?php echo $face_embeddings; ?></td></tr>
	    <tr><td>Url Foto</td><td><?php echo $url_foto; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('vektor_pegawai') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>
