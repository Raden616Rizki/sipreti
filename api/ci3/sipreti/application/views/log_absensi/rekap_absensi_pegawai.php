<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Rekapitulasi Absensi</title>
	<link rel="icon" href="<?= base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/form-styles.css?v=<?= time();'); ?>">

	<style>
		.absensi-info {
			display: flex;
			align-items: center;
			padding: 20px;
			background-color: white;
			border-bottom: 1px solid #ddd;
			font-size: 12px;
		}

		.absensi-info img {
			width: 120px;
			border-radius: 4px;
			margin-right: 20px;
			object-fit: cover;
		}

		.absensi-info div {
			line-height: 1.8;
		}

		.card {
			background-color: white;
			border-radius: 10px;
			width: 640px;
			box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
			overflow: hidden;
		}

		.table-container {
			padding-right: 24px;
			padding-left: 24px;
		}
	</style>
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

			<a class="active" href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="card-container">
		<div class="card">
			<div>
				<h2 class="card-header">Rekapitulasi Absensi</h2>
			</div>

			<div class="absensi-info">
				<img src="<?php echo $pegawai->url_foto ? base_url('uploads/foto_pegawai/' . $pegawai->url_foto) : base_url('assets/placeholder/default-profile.png'); ?>"
					alt="Foto Pegawai" class="profile-img" id="profilePreview"
					onerror="this.onerror=null; this.src='<?php echo base_url('assets/placeholder/default-profile.png'); ?>';">
				<div>
					<strong>Nama:</strong> <?= $pegawai->nama; ?><br>
					<strong>NIP:</strong> <?= $pegawai->nip; ?><br>
					<strong>Jabatan:</strong> <?= $pegawai->nama_jabatan; ?><br>
					<strong>Unit Kerja:</strong> <?= $pegawai->nama_unit_kerja; ?>
				</div>
			</div>
			<div class="table-container">
				<table class="data-table">
					<thead>
						<tr>
							<th>No.</th>
							<th>Hari</th>
							<th>Tanggal</th>
							<th>Jam Datang</th>
							<th>Jam Pulang</th>
						</tr>
					</thead>
					<tbody>
						<?php $no = 1;
						foreach ($absensi as $row): ?>
							<tr>
								<td><?= $no++; ?></td>
								<td><?= $row->hari; ?></td>
								<td><?= $row->tanggal; ?></td>
								<td><?= $row->jam_datang ? date('H.i', strtotime($row->jam_datang)) : '-'; ?></td>
								<td><?= $row->jam_pulang ? date('H.i', strtotime($row->jam_pulang)) : '-'; ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="form-actions">
				<a href="<?php echo site_url('log_absensi/list_pegawai') ?>" class="btn-cancel">Kembali</a>
			</div>
		</div>
	</div>
</body>

</html>
