<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Daftar Pegawai</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/biometric-styles.css?v=<?= time();'); ?>">
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
			<a href="/sipreti/vektor_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="content-container">
		<div class="section-header">
			<h2 class="section-title">Daftar Pegawai</h2>
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
				<form id="csvUploadForm" enctype="multipart/form-data" style="display: inline;">
					<input type="file" id="csvFileInput" accept=".csv" style="display: none;" />
					<button type="button" class="import-csv-btn"
						onclick="document.getElementById('csvFileInput').click()">Import CSV</button>
				</form>
				<a href="<?php echo site_url('pegawai/create'); ?>" class="add-btn">Tambah</a>
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
					<th>URL Foto</th>
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
						<td>
							<?php
							$foto_path = !empty($pegawai->url_foto)
								? base_url('uploads/foto_pegawai/' . $pegawai->url_foto)
								: base_url('assets/placeholder/default-profile.png');
							$default_foto = base_url('assets/placeholder/default-profile.png');
							?>
							<img src="<?php echo $foto_path; ?>" alt="Foto Pegawai"
								style="width: 64px; height: 64px; object-fit: cover; border-radius: 50%;"
								onerror="this.onerror=null; this.src='<?php echo $default_foto; ?>';">
						</td>

						<td class="action-buttons">
							<a href="<?php echo site_url('pegawai/read/' . $pegawai->id_pegawai); ?>"
								class="btn-action view"><i class="fas fa-eye"></i></a>
							<a href="<?php echo site_url('pegawai/update/' . $pegawai->id_pegawai); ?>"
								class="btn-action edit"><i class="fas fa-pen"></i></a>
							<a href="javascript:void(0);"
								onclick="openModal('<?php echo site_url('pegawai/delete/' . $pegawai->id_pegawai); ?>')"
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

	<!-- Modal Umum -->
	<div id="modal-global" class="modal-message" style="display:none;">
		<div class="modal-message-content" style="text-align: center; padding: 20px;">

			<!-- Loader -->
			<div id="modal-loader" style="display: none;">
				<div class="loader" style="margin: 20px auto;"></div>
				<p>Memproses, mohon tunggu...</p>

				<!-- Progress Bar -->
				<div id="progress-wrapper"
					style="position: relative; margin-top: 20px; width: 100%; max-width: 400px; margin-left: auto; margin-right: auto; border: 1px solid #ccc; border-radius: 4px; overflow: hidden; height: 20px;">
					<div id="progress-bar" style="width: 0%; height: 100%; background-color: #4caf50;">
					</div>
					<div id="progress-text"
						style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; font-size: 12px; color: black; font-weight: bold;">
						0%
					</div>
				</div>

			</div>

			<!-- Pesan Error -->
			<div id="modal-error-message" style="display: none;">
				<p id="modal-error-text">Terjadi kesalahan</p>
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

		const taskId = 'task_' + Date.now();

		document.getElementById('csvFileInput').addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (!file) {
				alert('Silakan pilih file CSV terlebih dahulu.');
				return;
			}

			const formData = new FormData();
			formData.append('file', file);

			showModalLoading();

			document.getElementById('progress-wrapper').style.display = 'block';

			updateProgressBar(0);

			fetch(`http://127.0.0.1:8000/attendance/upload-csv-pegawai/?task_id=${taskId}`, {
				method: 'POST',
				body: formData
			})
				.then(response => response.json())
				.then(result => {
					if (result.message) {
						checkProgressDone = true;
						updateProgressBar(100);
						window.location.href = "<?php echo site_url('vektor_pegawai/list_pegawai'); ?>";
					} else {
						showModalError(result.error || 'Gagal memproses CSV.');
					}
				})
				.catch(err => {
					console.error('Gagal mengunggah CSV:', err);
					showModalError('Terjadi kesalahan saat mengunggah CSV.');
				});

			let checkProgressDone = false;

			const intervalId = setInterval(() => {
				if (checkProgressDone) {
					clearInterval(intervalId);
					return;
				}
				fetch(`http://127.0.0.1:8000/attendance/progress/${taskId}/`)
					.then(res => res.json())
					.then(data => {
						if (data.total > 0) {
							const percent = Math.floor((data.done / data.total) * 100);
							updateProgressBar(percent);
						}
					})
					.catch(err => console.log('Gagal cek progress:', err));
			}, 2000);
		});

		function updateProgressBar(percent) {
			const bar = document.getElementById('progress-bar');
			const text = document.getElementById('progress-text');

			if (bar && text) {
				bar.style.width = percent + '%';
				text.textContent = percent + '%';
			}
		}

		function showModalLoading() {
			const modal = document.getElementById('modal-global');
			document.getElementById('modal-loader').style.display = 'block';
			document.getElementById('modal-error-message').style.display = 'none';
			modal.style.display = 'block';
		}
	</script>
</body>

</html>
