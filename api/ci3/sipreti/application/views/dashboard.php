<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard Pemerintah Kota Malang</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css'); ?>">
</head>

<body>
	<video autoplay muted loop class="video-bg">
		<source src="<?php echo base_url('assets/videos/pemkot_dashboard.mp4'); ?>" type="video/mp4">
		Your browser does not support HTML5 video.
	</video>

	<div class="overlay"></div>

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
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai">Vektor Pegawai</a>
		</div>
	</div>

	<div class="content">
		<h1>Pemerintah Kota Malang</h1>
		<p>Pendidikan, Perdagangan dan Jasa, Ekonomi, Kreatif, Pariwisata</p>
	</div>

	<footer>
		©2025 BKPSDM Kota Malang
	</footer>

</body>

</html>
