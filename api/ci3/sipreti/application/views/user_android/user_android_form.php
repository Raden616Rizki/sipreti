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
        <h2 style="margin-top:0px">User_android <?php echo $button ?></h2>
        <form action="<?php echo $action; ?>" method="post">
	    <div class="form-group">
            <label for="int">Id Pegawai <?php echo form_error('id_pegawai') ?></label>
            <input type="text" class="form-control" name="id_pegawai" id="id_pegawai" placeholder="Id Pegawai" value="<?php echo $id_pegawai; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Username <?php echo form_error('username') ?></label>
            <input type="text" class="form-control" name="username" id="username" placeholder="Username" value="<?php echo $username; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Password <?php echo form_error('password') ?></label>
            <input type="text" class="form-control" name="password" id="password" placeholder="Password" value="<?php echo $password; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Email <?php echo form_error('email') ?></label>
            <input type="text" class="form-control" name="email" id="email" placeholder="Email" value="<?php echo $email; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">No Hp <?php echo form_error('no_hp') ?></label>
            <input type="text" class="form-control" name="no_hp" id="no_hp" placeholder="No Hp" value="<?php echo $no_hp; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Valid Hp <?php echo form_error('valid_hp') ?></label>
            <input type="text" class="form-control" name="valid_hp" id="valid_hp" placeholder="Valid Hp" value="<?php echo $valid_hp; ?>" />
        </div>
	    <div class="form-group">
            <label for="varchar">Imei <?php echo form_error('imei') ?></label>
            <input type="text" class="form-control" name="imei" id="imei" placeholder="Imei" value="<?php echo $imei; ?>" />
        </div>
	    <input type="hidden" name="id_user_android" value="<?php echo $id_user_android; ?>" /> 
	    <button type="submit" class="btn btn-primary"><?php echo $button ?></button> 
	    <a href="<?php echo site_url('user_android') ?>" class="btn btn-default">Cancel</a>
	</form>
    </body>
</html>
