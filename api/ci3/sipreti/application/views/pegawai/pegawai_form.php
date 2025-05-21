<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Pegawai</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/form-styles.css?v=<?= time();'); ?>">

	<style>
		.profile-container {
			position: relative;
			width: 120px;
			margin: 20px auto;
		}

		.profile-img {
			width: 120px;
			height: 120px;
			object-fit: cover;
			border-radius: 50%;
			border: 4px solid #eee;
			background: #eee;
		}

		.edit-icon {
			position: absolute;
			bottom: 0;
			right: 0;
			background: #9c27b0;
			color: white;
			border-radius: 50%;
			padding: 8px;
			cursor: pointer;
			border: 2px solid white;
		}

		input[type="file"] {
			display: none;
		}
	</style>
	</stylcard-container>
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
					<a href="/sipreti/unit_kerja">Unit Kerja</a>
					<a href="/sipreti/radius_absen">Radius Absen</a>
				</div>
			</div>
			<a href="/sipreti/log_absensi">Absensi</a>
			<a class="active" href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="card-container">
		<div class="card">
			<div>
				<h2 class="card-header">Kelola Pegawai</h2>
			</div>

			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
				<div class="profile-container">
					<img src="<?php echo $url_foto ? base_url('uploads/foto_pegawai/' . $url_foto) : base_url('assets/placeholder/default-profile.png'); ?>"
						alt="Foto Pegawai" class="profile-img" id="profilePreview"
						onerror="this.onerror=null; this.src='<?php echo base_url('assets/placeholder/default-profile.png'); ?>';">

					<label for="fileInput" class="edit-icon">
						<i class="fa fa-pencil"></i>
					</label>
					<input type="file" id="fileInput" name="url_foto" accept="image/*" onchange="previewImage(event)">
				</div>

				<div class="form-group">
					<label for="nip">NIP</label>
					<input type="text" class="form-control" name="nip" id="nip" placeholder="NIP"
						value="<?php echo $nip; ?>" />
					<small class="error-text"><?php echo form_error('nip'); ?></small>
				</div>
				<div class="form-group">
					<label for="nama">Nama Pegawai</label>
					<input type="text" class="form-control" name="nama" id="nama" placeholder="Nama Pegawai"
						value="<?php echo $nama; ?>" />
					<small class="error-text"><?php echo form_error('nama'); ?></small>
				</div>
				<div class="form-group">
					<label for="id_jabatan">Jabatan</label>
					<select name="id_jabatan" id="id_jabatan" class="form-control">
						<option value="">Pilih Jabatan</option>
						<?php foreach ($jabatan_options as $jabatan): ?>
							<option value="<?php echo $jabatan->id_jabatan; ?>" <?php echo ($jabatan->id_jabatan == $id_jabatan) ? 'selected' : ''; ?>>
								<?php echo $jabatan->nama_jabatan; ?>
							</option>
						<?php endforeach; ?>
					</select>
					<small class="error-text"><?php echo form_error('id_jabatan'); ?></small>
				</div>
				<div class="form-group">
					<label for="id_unit_kerja">Unit Kerja</label>
					<select name="id_unit_kerja" id="id_unit_kerja" class="form-control">
						<option value="">Pilih Unit Kerja</option>
						<?php foreach ($unit_kerja_options as $unit): ?>
							<option value="<?php echo $unit->id_unit_kerja; ?>" <?php echo ($unit->id_unit_kerja == $id_unit_kerja) ? 'selected' : ''; ?>>
								<?php echo $unit->nama_unit_kerja; ?>
							</option>
						<?php endforeach; ?>
					</select>
					<small class="error-text"><?php echo form_error('id_unit_kerja'); ?></small>
				</div>

				<input type="hidden" name="id_pegawai" value="<?php echo $id_pegawai; ?>" />
				<input type="hidden" name="existing_foto" value="<?php echo $url_foto; ?>" />

				<div class="form-actions">
					<a href="<?php echo site_url('pegawai') ?>" class="btn-cancel">Batal</a>
					<button type="submit" class="btn-save"><?php echo $button; ?></button>
				</div>
			</form>
		</div>
	</div>
</body>
<script>
	function previewImage(event) {
		const reader = new FileReader();
		reader.onload = function () {
			const output = document.getElementById('profilePreview');
			output.src = reader.result;
		};
		reader.readAsDataURL(event.target.files[0]);
	}
</script>

</html>
