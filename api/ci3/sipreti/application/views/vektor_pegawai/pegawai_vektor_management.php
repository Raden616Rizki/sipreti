<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Biometrik</title>
	<link rel="icon" href="<?= base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/form-styles.css?v=<?= time();'); ?>">

	<style>
		.biometrik-info {
			display: flex;
			align-items: center;
			padding: 20px;
			background-color: white;
			border-bottom: 1px solid #ddd;
			font-size: 12px;
		}

		.biometrik-info img {
			width: 120px;
			border-radius: 4px;
			margin-right: 20px;
			object-fit: cover;
		}

		.biometrik-info div {
			line-height: 1.8;
		}

		.card {
			background-color: white;
			border-radius: 10px;
			width: 640px;
			box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
			overflow: hidden;
		}

		.modal {
			display: none;
			position: fixed;
			z-index: 999;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.4);
		}

		.modal-message {
			position: fixed;
			z-index: 1000;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.4);
		}

		.modal-message-content {
			background-color: #fff;
			margin: 15% auto;
			padding: 20px;
			border: 1px solid #888;
			width: 300px;
			border-radius: 8px;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
		}

		/* Loader animasi */
		.loader {
			border: 6px solid #f3f3f3;
			border-top: 6px solid #089CCA;
			border-radius: 50%;
			width: 40px;
			height: 40px;
			animation: spin 1s linear infinite;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}


		.table-container {
			padding-right: 24px;
			padding-left: 24px;
		}

		.image-grid {
			display: grid;
			grid-template-columns: repeat(5, 1fr);
			gap: 16px;
			padding: 0 24px 24px;
		}

		.image-slot {
			position: relative;
			width: 100%;
			padding-top: 100%;
		}

		.image-wrapper {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: #eee;
			border: 1px solid #ccc;
			border-radius: 4px;
			overflow: hidden;
		}

		.image-wrapper img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.image-wrapper.kosong {
			background-color: #f0f0f0;
		}

		.delete-btn {
			position: absolute;
			bottom: 4px;
			right: 4px;
			background-color: #F15E3C;
			color: white;
			border: none;
			border-radius: 50%;
			width: 28px;
			height: 28px;
			display: flex;
			justify-content: center;
			align-items: center;
			cursor: pointer;
		}

		.upload-container {
			display: flex;
			justify-content: flex-end;
			padding: 0 24px 24px;
			margin-top: 20px;
		}

		.upload-button {
			background-color: #089CCA;
			color: white;
			padding: 10px 16px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			font-size: 14px;
			transition: background-color 0.3s ease;
		}

		.upload-button:hover {
			background-color: #067e9e;
		}

		.btn-cancel {
			background-color: #4caf50;
			color: white;
			border: none;
			padding: 8px 16px;
			cursor: pointer;
			border-radius: 5px;
			font-weight: bold;
		}

		.btn-back {
			background-color: #fff;
			border: 1px solid #b100b7;
			color: #b100b7;
			padding: 8px 20px;
			font-size: 12px;
			border-radius: 6px;
			text-decoration: none;
			font-weight: bold;
		}

		.btn-save {
			background-color: #b100b7;
			color: #fff;
			border: none;
			padding: 8px 20px;
			font-size: 12px;
			border-radius: 6px;
			cursor: pointer;
			font-weight: bold;
		}

		.btn-cancel:hover,
		.btn-back:hover,
		.btn-save:hover {
			opacity: 0.9;
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

			<a href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a class="active" href="/sipreti/vektor_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="card-container">
		<div class="card">
			<div>
				<h2 class="card-header">Daftar Biometrik Pegawai</h2>
			</div>

			<div class="biometrik-info">
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

			<div class="upload-container">
				<button class="upload-button" onclick="document.getElementById('uploadInput').click();">
					Upload Foto
				</button>
				<input type="file" id="uploadInput" accept="image/*" style="display: none;" multiple>
			</div>

			<div class="image-grid">
				<?php for ($i = 0; $i < 10; $i++): ?>
					<div class="image-slot">
						<?php if (!empty($biometrik[$i])): ?>
							<div class="image-wrapper">
								<?php
								$foto_path = !empty($biometrik[$i]->url_foto)
									? base_url('uploads/vektor_pegawai/' . $biometrik[$i]->id_pegawai . '/' . $biometrik[$i]->url_foto)
									: base_url('assets/placeholder/default-profile.png');
								$default_foto = base_url('assets/placeholder/default-profile.png');
								?>
								<img src="<?php echo $foto_path; ?>" alt="Foto Pegawai"
									onerror="this.onerror=null; this.src='<?php echo $default_foto; ?>';">
								<button class="delete-btn"
									onclick="openModal('<?= site_url('vektor_pegawai/delete_from_pegawai/' . $biometrik[$i]->id_pegawai . '/' . $biometrik[$i]->id_vektor_pegawai); ?>')">
									<i class="fa fa-trash"></i>
								</button>
							</div>
						<?php else: ?>
							<div class="image-wrapper kosong"></div>
						<?php endif; ?>
					</div>
				<?php endfor; ?>
			</div>

			<div class="form-actions">
				<a href="<?php echo site_url('vektor_pegawai/list_pegawai') ?>" class="btn-back">Kembali</a>
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
			</div>

			<!-- Pesan Error -->
			<div id="modal-error-message" style="display: none;">
				<p id="modal-error-text">Terjadi kesalahan</p>
			</div>
		</div>
	</div>

</body>
<script>
	// Ganti dengan ID pegawai yang aktif, bisa diatur via server-side templating
	const idPegawai = "<?= $pegawai->id_pegawai ?>";

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

	document.getElementById('uploadInput').addEventListener('change', function (event) {
		const files = event.target.files;

		if (!files || files.length === 0) {
			alert("Tidak ada file yang dipilih.");
			return;
		}

		showModalLoading(); // tampilkan loader

		let uploadsSelesai = 0;

		for (let i = 0; i < files.length; i++) {
			const formData = new FormData();
			formData.append('id_pegawai', idPegawai);
			formData.append('uploaded_file', files[i]);

			fetch('http://127.0.0.1:8000/attendance/face-register/', {
				method: 'POST',
				body: formData
			})
				.then(response => {
					return response.json().then(data => ({
						status: response.status,
						ok: response.ok,
						data: data
					}));
				})
				.then(result => {
					uploadsSelesai++;

					if (!result.ok || !result.data.message) {
						showModalError(result.data.error || 'Terjadi kesalahan saat mengunggah gambar');
					}

					// Jika semua upload selesai dan tidak ada error, reload halaman
					if (uploadsSelesai === files.length && result.ok && result.data.message) {
						window.location.href = "<?= site_url('vektor_pegawai/read_vektor_pegawai/' . $pegawai->id_pegawai) ?>";
					}
				})
				.catch(error => {
					console.error(`Error saat upload gambar ke-${i + 1}:`, error);
					showModalError('Terjadi kesalahan saat mengunggah gambar');
				});
		}
	});

	// Fungsi modal loading
	function showModalLoading() {
		const modal = document.getElementById('modal-global');
		document.getElementById('modal-loader').style.display = 'block';
		document.getElementById('modal-error-message').style.display = 'none';
		modal.style.display = 'block';
	}

	// Fungsi modal error
	function showModalError(pesan) {
		const modal = document.getElementById('modal-global');
		document.getElementById('modal-loader').style.display = 'none';
		const errorBox = document.getElementById('modal-error-message');
		const errorText = document.getElementById('modal-error-text');

		errorText.textContent = pesan;
		errorBox.style.display = 'block';
		modal.style.display = 'block';

		setTimeout(() => {
			modal.style.display = 'none';
		}, 2000);
	}


</script>

</html>
