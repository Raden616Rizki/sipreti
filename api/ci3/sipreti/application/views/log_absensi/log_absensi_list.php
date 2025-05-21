<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Daftar Log Absensi</title>
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
			<a class="active" href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="content-container">
		<div class="section-header">
			<h2 class="section-title">Daftar Log Absensi</h2>
		</div>

		<div class="toolbar">
			<form action="<?php echo site_url('log_absensi/index'); ?>" method="get" class="search-form">
				<input type="text" name="q" class="search-input" placeholder="Cari nama pegawai..."
					value="<?php echo $q; ?>">
				<button type="submit" class="search-btn">Cari</button>
				<?php if ($q <> ''): ?>
					<a href="<?php echo site_url('log_absensi'); ?>" class="reset-btn">Reset</a>
				<?php endif; ?>
			</form>
			<a href="<?php echo site_url('log_absensi/list_pegawai'); ?>" class="add-btn">Rekap Absensi Pegawai</a>
		</div>

		<table class="data-table">
			<thead>
				<tr>
					<th>No</th>
					<th>Nama Pegawai</th>
					<th>Jenis Absensi</th>
					<th>Check Mode</th>
					<th>Waktu Absensi</th>
					<!-- <th>Latitude</th>
					<th>Longitude</th> -->
					<th>Nama Lokasi</th>
					<th>Foto Presensi</th>
					<th>Dokumen</th>
					<th>Waktu Verifikasi</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($log_absensi_data as $log_absensi): ?>
					<tr>
						<td><?php echo ++$start; ?></td>
						<td><?php echo $log_absensi->nama; ?></td>
						<td><?php echo $log_absensi->jenis_absensi == 1 ? 'DD/DL' : 'Reguler'; ?></td>
						<td><?php echo $log_absensi->check_mode == 1 ? 'Check Out' : 'Check In'; ?></td>
						<td><?php echo $log_absensi->waktu_absensi; ?></td>
						<!-- <td><?php echo $log_absensi->lattitude; ?></td>
						<td><?php echo $log_absensi->longitude; ?></td> -->
						<td style="max-width: 200px; word-break: break-word;"><?php echo $log_absensi->nama_lokasi; ?></td>
						<td>
							<?php
							$foto_path = FCPATH . 'uploads/presensi/' . $log_absensi->id_pegawai . '/' . $log_absensi->url_foto_presensi;
							if (!empty($log_absensi->url_foto_presensi) && file_exists($foto_path)) {
								$foto_url = base_url('uploads/presensi/' . $log_absensi->id_pegawai . '/' . $log_absensi->url_foto_presensi);
								echo '<img src="' . $foto_url . '" alt="Foto Presensi" width="96" style="object-fit:cover;">';
							}
							?>
						</td>
						<td>
							<?php
							$dokumen_path = FCPATH . 'uploads/presensi/' . $log_absensi->id_pegawai . '/' . $log_absensi->url_dokumen;
							if (!empty($log_absensi->url_dokumen) && file_exists($dokumen_path)) {
								$dokumen_url = base_url('uploads/presensi/' . $log_absensi->id_pegawai . '/' . $log_absensi->url_dokumen);
								echo '<a href="' . $dokumen_url . '" target="_blank">Lihat Dokumen</a>';
							}
							?>
						</td>
						<td><?php echo $log_absensi->waktu_verifikasi; ?></td>
						<td class="action-buttons">
							<!-- <a href="<?php echo site_url('log_absensi/read/' . $log_absensi->id_log_absensi); ?>"
								class="btn-action view"><i class="fas fa-eye"></i></a>
							<a href="<?php echo site_url('log_absensi/update/' . $log_absensi->id_log_absensi); ?>"
								class="btn-action edit"><i class="fas fa-pen"></i></a> -->
							<a href="javascript:void(0);"
								onclick="openModal('<?php echo site_url('log_absensi/delete/' . $log_absensi->id_log_absensi); ?>')"
								class="btn-action delete"><i class="fas fa-trash-alt"></i></a>
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

		window.onclick = function (event) {
			const modal = document.getElementById('confirmModal');
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
	</script>
</body>

</html>
