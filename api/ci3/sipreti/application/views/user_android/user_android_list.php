<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Daftar User Android</title>
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
			<a class="active" href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="content-container">
		<div class="section-header">
			<h2 class="section-title">Daftar User Android</h2>
		</div>

		<div class="toolbar">
			<form action="<?php echo site_url('user_android/index'); ?>" method="get" class="search-form">
				<input type="text" name="q" class="search-input" placeholder="Masukkan kata kunci..."
					value="<?php echo $q; ?>">
				<button type="submit" class="search-btn">Cari</button>
				<?php if ($q <> ''): ?>
					<a href="<?php echo site_url('user_android'); ?>" class="reset-btn">Reset</a>
				<?php endif; ?>
			</form>
		</div>

		<table class="data-table">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama Pegawai</th>
					<th>Username</th>
					<th>Email</th>
					<th>No HP</th>
					<th>Valid HP</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($user_android_data as $user_android): ?>
					<tr>
						<td><?php echo ++$start; ?></td>
						<td style="max-width: 200px; word-break: break-word;"><?php echo $user_android->nama; ?></td>
						<td style="max-width: 200px; word-break: break-word;"><?php echo $user_android->username; ?></td>
						<td><?php echo $user_android->email; ?></td>
						<td><?php echo $user_android->no_hp; ?></td>
						<td><?php echo $user_android->valid_hp; ?></td>
						<td class="action-buttons">
							<a href="<?php echo site_url('user_android/read/' . $user_android->id_user_android); ?>"
								class="btn-action view"><i class="fas fa-eye"></i></a>
							<a href="javascript:void(0);"
								onclick="openModal('<?php echo site_url('user_android/delete/' . $user_android->id_user_android); ?>')"
								class="btn-action delete"><i class="fas fa-trash-alt"></i></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="pagination-footer">
			<div>Total Record: <strong><?php echo $total_rows; ?></strong></div>
			<div><?php echo $pagination; ?></div>
		</div>
	</div>

	<footer class="footer">
		&copy;2025 BKPSDM Kota Malang
	</footer>

	<!-- Modal Konfirmasi -->
	<div id="confirmModal" class="modal">
		<div class="modal-content">
			<p>Apakah anda yakin menghapus data ini?</p>
			<div class="modal-actions">
				<button onclick="closeModal()" class="btn-cancel">Batal</button>
				<a href="#" id="confirmDeleteBtn" class="btn-delete">Hapus</a>
			</div>
		</div>
	</div>

	<script>
		function openModal(deleteUrl) {
			document.getElementById('confirmModal').style.display = 'block';
			document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
		}

		function closeModal() {
			document.getElementById('confirmModal').style.display = 'none';
		}

		window.onclick = function (event) {
			const modal = document.getElementById('confirmModal');
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
	</script>
</body>

</html>
