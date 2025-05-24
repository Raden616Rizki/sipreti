<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Daftar Vektor Pegawai</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?= base_url('assets/css/biometric-styles.css?v=<?= time();'); ?>">

	<style>
		.modal-detail {
			position: fixed;
			z-index: 9999;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.4);
		}

		.modal-content-detail {
			background-color: #fff;
			margin: 10% auto;
			padding: 20px;
			border-radius: 10px;
			width: 80%;
			max-width: 800px;
		}

		.close {
			float: right;
			font-size: 28px;
			font-weight: bold;
			cursor: pointer;
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
			<a class="active" href="/sipreti/vektor_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="content-container">
		<div class="section-header">
			<h2 class="section-title">Daftar Vektor Pegawai</h2>
		</div>

		<div class="toolbar">
			<form action="<?php echo site_url('vektor_pegawai/index'); ?>" method="get" class="search-form">
				<input type="text" name="q" class="search-input" placeholder="Cari Nama Pegawai..."
					value="<?php echo $q; ?>">
				<button type="submit" class="search-btn">Cari</button>
				<?php if ($q <> ''): ?>
					<a href="<?php echo site_url('vektor_pegawai'); ?>" class="reset-btn">Reset</a>
				<?php endif; ?>
			</form>
			<div>
				<button type="button" class="import-csv-btn" onclick="showROC()">Tampilkan ROC</button>
				<button type="button" class="export-csv-btn" onclick="showAccuracy()">Tampilkan Akurasi</button>
				<a href="<?php echo site_url('vektor_pegawai/list_pegawai'); ?>" class="add-btn">Kelola Biometrik</a>
			</div>
		</div>

		<table class="data-table">
			<thead>
				<tr>
					<th>No.</th>
					<th>Nama Pegawai</th>
					<th>Face Embeddings</th>
					<th>Foto</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($vektor_pegawai_data as $vektor_pegawai): ?>
					<tr>
						<td><?php echo ++$start ?></td>
						<td><?php echo $vektor_pegawai->nama ?></td>
						<td style="max-width: 300px; word-break: break-all;"><?php echo $vektor_pegawai->face_embeddings ?>
						</td>
						<td>
							<?php
							$foto_path = !empty($vektor_pegawai->url_foto)
								? base_url('uploads/vektor_pegawai/' . $vektor_pegawai->id_pegawai . '/' . $vektor_pegawai->url_foto)
								: base_url('assets/placeholder/default-profile.png');
							$default_foto = base_url('assets/placeholder/default-profile.png');
							?>
							<img src="<?php echo $foto_path; ?>" alt="Foto Pegawai"
								style="width: 100px; border-radius: 8px;"
								onerror="this.onerror=null; this.src='<?php echo $default_foto; ?>';">
						</td>
						<td class="action-buttons">
							<a href="javascript:void(0);"
								onclick="openModal('<?php echo site_url('vektor_pegawai/delete/' . $vektor_pegawai->id_vektor_pegawai); ?>')"
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
			</div>

			<!-- Pesan Error -->
			<div id="modal-error-message" style="display: none;">
				<p id="modal-error-text">Terjadi kesalahan</p>
			</div>
		</div>
	</div>

	<div id="rocModal" class="modal-detail" style="display:none;">
		<div class="modal-content-detail">
			<span class="close" onclick="document.getElementById('rocModal').style.display='none'">&times;</span>
			<h4 style="margin-bottom: 16px;">Kurva ROC (Receiver Operating Characteristic)</h4>
			<hr>
			<div id="rocContent"></div>
		</div>
	</div>

	<div id="accuracyModal" class="modal-detail" style="display:none;">
		<div class="modal-content-detail">
			<span class="close" onclick="document.getElementById('accuracyModal').style.display='none'">&times;</span>
			<h4 style="margin-bottom: 16px;">Evaluasi Akurasi Face Recognition</h4>
			<hr>
			<div id="accuracyContent">Memuat data akurasi...</div>
		</div>
	</div>
</body>
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

	// Fungsi modal loading
	function showModalLoading() {
		const modal = document.getElementById('modal-global');
		document.getElementById('modal-loader').style.display = 'block';
		document.getElementById('modal-error-message').style.display = 'none';
		modal.style.display = 'block';
	}

	function showROC() {
		const modal = document.getElementById('rocModal');
		const content = document.getElementById('rocContent');
		content.innerHTML = '<p style="font-size: 12px;">Memuat data ROC...</p>';

		fetch('http://127.0.0.1:8000/attendance/evaluate-roc-curve/')
			.then(response => response.json())
			.then(data => {
				const { manhattan, euclidean } = data;

				content.innerHTML = `
				<div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 16px; font-size: 12px;">
					<div style="flex: 1; min-width: 300px;">
						<h4 style="font-size: 12px;">Metode Euclidean</h4>
						<p><strong>AUC:</strong> ${euclidean.auc}</p>
						<p style="margin-bottom: 24px;"><strong>Optimal Threshold:</strong> ${euclidean.optimal_threshold}</p>
						<img src="${euclidean.roc_curve_image}" alt="ROC Euclidean" 
							style="width: 100%; border:1px solid #ccc; margin-bottom: 12px;" />
						<img src="${euclidean.threshold_plot_image}" alt="Threshold Euclidean" 
							style="width: 100%; border:1px solid #ccc;" />
					</div>

					<div style="flex: 1; min-width: 300px;">
						<h4 style="font-size: 12px;">Metode Manhattan</h4>
						<p><strong>AUC:</strong> ${manhattan.auc}</p>
						<p style="margin-bottom: 24px;"><strong>Optimal Threshold:</strong> ${manhattan.optimal_threshold}</p>
						<img src="${manhattan.roc_curve_image}" alt="ROC Manhattan" 
							style="width: 100%; border:1px solid #ccc; margin-bottom: 12px;" />
						<img src="${manhattan.threshold_plot_image}" alt="Threshold Manhattan" 
							style="width: 100%; border:1px solid #ccc;" />
					</div>
				</div>
			`;
			})
			.catch(err => {
				console.error(err);
				content.innerHTML = '<p style="color:red; font-size: 12px;">Gagal memuat data ROC.</p>';
			});

		modal.style.display = 'block';
	}

	function showAccuracy() {
		const modal = document.getElementById('accuracyModal');
		const content = document.getElementById('accuracyContent');
		content.innerHTML = '<p style="font-size: 12px;">Memuat data akurasi...</p>';

		fetch('http://127.0.0.1:8000/attendance/evaluate-face-recognition/')
			.then(response => response.json())
			.then(data => {
				const { statistics, manhattan, euclidean, plots } = data;

				function generateTable(dataByThreshold) {
					const headers = ['Threshold', 'Accuracy', 'Precision', 'Recall', 'F1', 'TP', 'FP', 'TN', 'FN', 'Total'];
					const rows = Object.entries(dataByThreshold).map(([threshold, values]) => {
						return `
						<tr>
							<td>${threshold}</td>
							<td>${values.accuracy}</td>
							<td>${values.precision}</td>
							<td>${values.recall}</td>
							<td>${values.f1}</td>
							<td>${values.true_positive}</td>
							<td>${values.false_positive}</td>
							<td>${values.true_negative}</td>
							<td>${values.false_negative}</td>
							<td>${values.total_pairs}</td>
						</tr>`;
					}).join('');

					return `
					<table border="1" cellpadding="6" cellspacing="0" 
						style="border-collapse: collapse; font-size: 12px; width: 90%; margin: 0 auto 12px auto; text-align: center;">
						<thead style="background-color: #eee;">
							<tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr>
						</thead>
						<tbody>${rows}</tbody>
					</table>`;
				}

				content.innerHTML = `
				<div style="font-size: 12px; margin-top: 16px;">
					<h5 style="margin-bottom: 4px; font-size: 12px;">Statistik Dataset</h5>
					<p><strong>Total Embeddings:</strong> ${statistics.total_embeddings}</p>
					<p><strong>Total Pegawai (Label):</strong> ${statistics.total_labels}</p>

					<hr style="margin: 12px 0;" />

					<h5 style="font-size: 12px;">Distribusi Embedding per Pegawai</h5>
					<img src="data:image/png;base64,${plots.embeddings_per_label}" alt="Distribusi Embedding"
						style="width: 100%; border:1px solid #ccc; margin-bottom: 16px;" />

					<h4 style="margin-top: 24px; font-size: 12px;">Evaluasi Metode Euclidean</h4>
					${generateTable(euclidean)}
					<img src="data:image/png;base64,${plots.euclidean}" alt="Plot Euclidean"
						style="width: 70%; border:1px solid #ccc; display: block; margin: 12px auto;" />

					<h4 style="margin-top: 24px; font-size: 12px;">Evaluasi Metode Manhattan</h4>
					${generateTable(manhattan)}
					<img src="data:image/png;base64,${plots.manhattan}" alt="Plot Manhattan"
						style="width: 70%; border:1px solid #ccc; display: block; margin: 12px auto;" />
				</div>
			`;
			})
			.catch(err => {
				console.error(err);
				content.innerHTML = '<p style="color:red; font-size: 12px;">Gagal memuat data akurasi.</p>';
			});

		modal.style.display = 'block';
	}
</script>

</html>
