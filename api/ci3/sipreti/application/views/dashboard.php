<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard Pemerintah Kota Malang</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">

	<style>
		body,
		html {
			height: 100%;
			font-family: "Poppins", sans-serif;
			margin: 0;
			padding: 0;
			color: white;
			background-color: transparent;
		}

		.content {
			position: relative;
			z-index: 1;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			height: 100vh;
			text-align: center;
		}

		/* Video Background */
		.video-bg {
			position: fixed;
			top: 0;
			left: 0;
			width: 100vw;
			height: 100vh;
			object-fit: cover;
			z-index: -1;
		}

		.overlay {
			position: absolute;
			top: 0;
			left: 0;
			height: 100%;
			width: 100%;
			background-color: rgba(0, 0, 0, 0.5);
			z-index: 0;
		}

		footer {
			position: absolute;
			bottom: 10px;
			width: 100%;
			text-align: center;
			color: #ccc;
			z-index: 2;
			font-size: 12px;
		}
	</style>
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
			<a class="active" href="/sipreti/dashboard">Dashboard</a>

			<div class="dropdown">
				<a href="#" class="dropdown-toggle">Data Master ▾</a>
				<div class="dropdown-menu">
					<a href="/sipreti/jabatan">Jabatan</a>
					<a href="/sipreti/unit_kerja">Unit Kerja</a>
					<a href="/sipreti/radius_absen">Radius Absen</a>
				</div>
			</div>

			<a href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
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
