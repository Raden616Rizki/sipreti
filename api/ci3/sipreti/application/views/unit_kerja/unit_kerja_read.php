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
        <h2 style="margin-top:0px">Unit_kerja Read</h2>
        <table class="table">
	    <tr><td>Id Radius</td><td><?php echo $id_radius; ?></td></tr>
	    <tr><td>Nama Unit Kerja</td><td><?php echo $nama_unit_kerja; ?></td></tr>
	    <tr><td>Alamat</td><td><?php echo $alamat; ?></td></tr>
	    <tr><td>Lattitude</td><td><?php echo $lattitude; ?></td></tr>
	    <tr><td>Longitude</td><td><?php echo $longitude; ?></td></tr>
	    <tr><td>Created At</td><td><?php echo $created_at; ?></td></tr>
	    <tr><td>Updated At</td><td><?php echo $updated_at; ?></td></tr>
	    <tr><td>Deleted At</td><td><?php echo $deleted_at; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('unit_kerja') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>