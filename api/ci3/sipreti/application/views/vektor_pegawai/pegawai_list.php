<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Daftar Biometrik Pegawai</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
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
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a class="active" href="/sipreti/vektor_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="content-container">
		<div class="section-header">
			<h2 class="section-title">Biometrik Pegawai</h2>
		</div>

		<div class="toolbar">
			<form action="<?php echo site_url('pegawai/index'); ?>" method="get" class="search-form">
				<input type="text" name="q" class="search-input" placeholder="Masukkan nama pegawai..."
					value="<?php echo $q; ?>">
				<button type="submit" class="search-btn">Cari</button>
				<?php if ($q <> ''): ?>
					<a href="<?php echo site_url('pegawai'); ?>" class="reset-btn">Reset</a>
				<?php endif; ?>
			</form>
			<div>
				<a href="<?php echo site_url('vektor_pegawai/list_pegawai'); ?>" class="export-csv-btn">Export CSV</a>
				<a href="<?php echo site_url('vektor_pegawai/list_pegawai'); ?>" class="import-csv-btn">Import CSV</a>
			</div>
		</div>

		<table class="data-table">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama</th>
					<th>NIP</th>
					<th>Jabatan</th>
					<th>Unit Kerja</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($pegawai_data as $pegawai): ?>
					<tr>
						<td><?php echo ++$start; ?></td>
						<td style="max-width: 200px; word-break: break-word;"><?php echo $pegawai->nama; ?></td>
						<td><?php echo $pegawai->nip; ?></td>
						<td><?php echo $pegawai->nama_jabatan; ?></td>
						<td style="max-width: 200px; word-break: break-word;"><?php echo $pegawai->nama_unit_kerja; ?></td>

						<td class="action-buttons">
							<a href="<?php echo site_url('log_absensi/read_absensi_pegawai/' . $pegawai->id_pegawai); ?>"
								class="btn-action view" style="font-size: 12px;">Rekap Absensi</i></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="pagination-footer">
			<div>Total Record: <strong><?= $total_rows; ?></strong></div>
			<div class="custom-pagination-wrapper">
				<div><?php echo $pagination; ?></div>
			</div>
		</div>
	</div>

	<footer class="footer">
		&copy;2025 BKPSDM Kota Malang
	</footer>
</body>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		const wrapper = document.querySelector('.custom-pagination-wrapper');

		if (!wrapper) return;

		const strongs = wrapper.querySelectorAll('strong');
		strongs.forEach(strong => {
			const activeText = strong.textContent.trim();
			const activeLink = document.createElement('a');
			activeLink.textContent = activeText;
			activeLink.href = '#';
			activeLink.className = 'active-page';
			strong.replaceWith(activeLink);
		});
	});
</script>

</html>
