<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Radius Absen</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/form-styles.css?v=<?= time();'); ?>">
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
					<a class="active-sub" href="/sipreti/radius_absen">Radius Absen</a>
				</div>
			</div>

			<a href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="card-container">
		<div class="card">
			<div>
				<h2 class="card-header">Kelola Radius Absen</h2>
			</div>
			<form action="<?php echo $action; ?>" method="post">
				<div class="form-group">
					<label for="float">Radius Abesn (Meter) <?php echo form_error('ukuran') ?></label>
					<input type="text" class="form-control" name="ukuran" id="ukuran"
						placeholder="Masukkan radius absen" value="<?php echo $ukuran; ?>" />
				</div>
				<input type="hidden" name="id_radius" value="<?php echo $id_radius; ?>" />
				<div class="form-actions">
					<a href="<?php echo site_url('radius_absen') ?>" class="btn-cancel">Batal</a>
					<button type="submit" class="btn-save"><?php echo $button ?></button>
				</div>
			</form>
		</div>
	</div>
</body>

<div id="modal-error" class="modal" style="display:none;">
	<div class="modal-content">
		<p>Masukkan nilai angka</p>
		<div class="modal-actions" style="text-align: right; margin-top: 15px;">
			<button onclick="closeModal()" class="btn-cancel">Tutup</button>
		</div>
	</div>
</div>

<script>
	function closeModal() {
		document.getElementById('modal-error').style.display = 'none';
	}

	document.querySelector('form').addEventListener('submit', function (e) {
		const input = document.getElementById('ukuran').value.trim();
		if (isNaN(input) || input === "") {
			e.preventDefault();
			document.getElementById('modal-error').style.display = 'block';
		}
	});
</script>

</html>
