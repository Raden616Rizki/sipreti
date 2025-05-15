<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Daftar Unit Kerja</title>
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

	<div class="content-container">
		<div class="section-header">
			<h2 class="section-title">Daftar Unit Kerja</h2>
		</div>

		<div class="toolbar">
			<form action="<?php echo site_url('unit_kerja/index'); ?>" method="get" class="search-form">
				<input type="text" name="q" class="search-input" placeholder="Masukkan unit kerja..."
					value="<?php echo $q; ?>">
				<button type="submit" class="search-btn">Cari</button>
				<?php if ($q <> ''): ?>
					<a href="<?php echo site_url('unit_kerja'); ?>" class="reset-btn">Reset</a>
				<?php endif; ?>
			</form>
			<a href="<?php echo site_url('unit_kerja/create'); ?>" class="add-btn">Tambah</a>
		</div>

		<table class="data-table">
			<thead>
				<tr>
					<th>No</th>
					<th>Radius</th>
					<th>Nama Unit Kerja</th>
					<th>Alamat</th>
					<th>Lattitude</th>
					<th>Longitude</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($unit_kerja_data as $unit_kerja): ?>
					<tr>
						<td><?php echo ++$start ?></td>
						<td><?php echo $unit_kerja->ukuran ?> meter</td>
						<td style="max-width: 200px; word-break: break-word;"><?php echo $unit_kerja->nama_unit_kerja ?>
						</td>
						<td style="max-width: 200px; word-break: break-word;"><?php echo $unit_kerja->alamat ?></td>
						<td><?php echo $unit_kerja->lattitude ?></td>
						<td><?php echo $unit_kerja->longitude ?></td>
						<td class="action-buttons">
							<a href="<?php echo site_url('unit_kerja/read/' . $unit_kerja->id_unit_kerja); ?>"
								class="btn-action view"><i class="fas fa-eye"></i></a>
							<a href="<?php echo site_url('unit_kerja/update/' . $unit_kerja->id_unit_kerja); ?>"
								class="btn-action edit"><i class="fas fa-pen"></i></a>
							<a href="javascript:void(0);"
								onclick="openModal('<?php echo site_url('unit_kerja/delete/' . $unit_kerja->id_unit_kerja); ?>')"
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
