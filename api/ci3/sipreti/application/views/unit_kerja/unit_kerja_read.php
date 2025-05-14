<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Jabatan</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/form-styles.css'); ?>">
</head>

<body>
	<div class="navbar">
		<div class="navbar-left">
			<img src="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>" alt="Logo" class="logo" />
			<div class="brand-text">
				<div class="brand-title">SI Preti</div>
				<div class="brand-subtitle">Sistem Informasi Presensi Terkini</div>
			</div>
		</div>
		<div class="navbar-right">
			<a href="/sipreti/dashboard">Dashboard</a>

			<div class="dropdown">
				<a href="/sipreti/dashboard" class="dropdown-toggle">Data Master ▾</a>
				<div class="dropdown-menu">
					<a href="/sipreti/jabatan">Jabatan</a>
					<a class="active-sub" href="/sipreti/unit_kerja">Unit Kerja</a>
					<a href="/sipreti/radius_absen">Radius Absen</a>
				</div>
			</div>

			<a href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="card-container">
		<div class="card">
			<div>
				<h2 class="card-header">Detail Unit Kerja</h2>
			</div>
			<div class="form-group">
				<label for="id_radius">Radius Absen (Meter)</label>
				<select name="id_radius" id="id_radius" class="form-control" disabled>
					<option value="">Pilih Radius</option>
					<?php foreach ($radius_options as $radius): ?>
						<option value="<?php echo $radius->id_radius; ?>" <?php echo ($radius->id_radius == $id_radius) ? 'selected' : ''; ?>>
							<?php echo $radius->ukuran; ?>
						</option>
					<?php endforeach; ?>
				</select>
				<small class="error-text"><?php echo form_error('id_radius'); ?></small>
			</div>

			<div class="form-group">
				<label for="nama_unit_kerja">Nama Unit Kerja</label>
				<input type="text" class="form-control" name="nama_unit_kerja" id="nama_unit_kerja"
					placeholder="Nama Unit Kerja" value="<?php echo $nama_unit_kerja; ?>" readonly/>
				<small class="error-text"><?php echo form_error('nama_unit_kerja'); ?></small>
			</div>

			<div class="form-group">
				<div class="label-with-icon">
					<label for="alamat">Alamat</label>
				</div>
				<textarea class="form-control" name="alamat" id="alamat"
					placeholder="Masukkan alamat" disabled><?php echo $alamat;?></textarea>
				<small class="error-text"><?php echo form_error('alamat'); ?></small>
			</div>

			<div class="form-group">
				<label for="lattitude">Lattitude</label>
				<input type="text" class="form-control" name="lattitude" id="lattitude" placeholder="Lattitude"
					value="<?php echo $lattitude; ?>" readonly/>
				<small class="error-text"><?php echo form_error('lattitude'); ?></small>
			</div>

			<div class="form-group">
				<label for="longitude">Longitude</label>
				<input type="text" class="form-control" name="longitude" id="longitude" placeholder="Longitude"
					value="<?php echo $longitude; ?>" readonly/>
				<small class="error-text"><?php echo form_error('longitude'); ?></small>
			</div>

			<input type="hidden" name="id_unit_kerja" value="<?php echo $id_unit_kerja; ?>" />
			<div class="form-actions">
				<a href="<?php echo site_url('unit_kerja') ?>" class="btn-cancel">Batal</a>
			</div>
		</div>
	</div>
</body>

</html>
