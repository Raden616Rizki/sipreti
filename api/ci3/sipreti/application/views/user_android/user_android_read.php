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
        <h2 style="margin-top:0px">User_android Read</h2>
        <table class="table">
	    <tr><td>Id Pegawai</td><td><?php echo $id_pegawai; ?></td></tr>
	    <tr><td>Username</td><td><?php echo $username; ?></td></tr>
	    <tr><td>Email</td><td><?php echo $email; ?></td></tr>
	    <tr><td>No Hp</td><td><?php echo $no_hp; ?></td></tr>
	    <tr><td>Valid Hp</td><td><?php echo $valid_hp; ?></td></tr>
	    <tr><td>Imei</td><td><?php echo $imei; ?></td></tr>
	    <tr><td>Created At</td><td><?php echo $created_at; ?></td></tr>
	    <tr><td>Updated At</td><td><?php echo $updated_at; ?></td></tr>
	    <tr><td>Deleted At</td><td><?php echo $deleted_at; ?></td></tr>
	    <tr><td></td><td><a href="<?php echo site_url('user_android') ?>" class="btn btn-default">Cancel</a></td></tr>
	</table>
        </body>
</html>
