<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kelola Unit Kerja</title>
	<link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/form-styles.css?v=<?= time();'); ?>">

	<!-- Leaflet -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

	<!-- Leaflet GeoSearch -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet-geosearch@3.1.0/dist/geosearch.css" />
	<script src="https://unpkg.com/leaflet-geosearch@3.1.0/dist/bundle.min.js"></script>

	<style>
		.modal {
			display: none;
			position: fixed;
			z-index: 9999;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgba(0, 0, 0, 0.4);
		}

		.modal-content {
			background-color: #f7f7f7;
			margin: 5% auto;
			padding: 20px;
			border-radius: 10px;
			width: 90%;
			max-width: 800px;
			box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
			font-family: sans-serif;
		}

		/* Close button */
		.close {
			float: right;
			font-size: 28px;
			font-weight: bold;
			cursor: pointer;
		}

		#searchBox {
			width: 85%;
			padding: 10px 15px;
			border: 1px solid #ccc;
			border-radius: 8px 0 0 8px;
			outline: none;
			font-size: 12px;
		}

		button[onclick="searchLocation()"] {
			background-color: #9c27b0;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 0 8px 8px 0;
			cursor: pointer;
			font-weight: bold;
			font-size: 12px;
		}

		#map {
			height: 400px;
			margin-top: 15px;
			border-radius: 8px;
			overflow: hidden;
		}

		#selectedAddress {
			background-color: #fff;
			padding: 15px;
			border-radius: 10px;
			box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
			margin-top: 15px;
			font-size: 14px;
			font-weight: 500;
		}

		.modal-footer {
			display: flex;
			justify-content: flex-end;
			margin-top: 15px;
			gap: 10px;
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
					<a class="active-sub" href="/sipreti/unit_kerja">Unit Kerja</a>
					<a href="/sipreti/radius_absen">Radius Absen</a>
				</div>
			</div>

			<a href="/sipreti/log_absensi">Absensi</a>
			<a href="/sipreti/pegawai">Pegawai</a>
			<a href="/sipreti/user_android">User Android</a>
			<a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
		</div>
	</div>

	<div class="card-container">
		<div class="card">
			<div>
				<h2 class="card-header">Kelola Unit Kerja</h2>
			</div>
			<form action="<?php echo $action; ?>" method="post">
				<div class="form-group">
					<label for="id_radius">Radius Absen (Meter)</label>
					<select name="id_radius" id="id_radius" class="form-control">
						<option value="">Pilih Radius</option>
						<?php foreach ($radius_options as $radius): ?>
							<option value="<?php echo $radius->id_radius; ?>" <?php echo ($radius->id_radius == $id_radius) ? 'selected' : ''; ?>>
								<?php echo $radius->ukuran; ?>
							</option>
						<?php endforeach; ?>
					</select>
					<small class="error-text"><?php echo form_error('id_radius'); ?></small>
				</div>

				<div class="form-group">
					<label for="nama_unit_kerja">Nama Unit Kerja</label>
					<input type="text" class="form-control" name="nama_unit_kerja" id="nama_unit_kerja"
						placeholder="Nama Unit Kerja" value="<?php echo $nama_unit_kerja; ?>" />
					<small class="error-text"><?php echo form_error('nama_unit_kerja'); ?></small>
				</div>

				<div class="form-group">
					<div class="label-with-icon">
						<label for="alamat">Alamat</label>
						<span class="icon-map" onclick="openMapModal()">
							<i class="fas fa-map-marked-alt"></i>
						</span>
					</div>
					<textarea class="form-control" name="alamat" id="alamat"
						placeholder="Masukkan alamat"><?php echo $alamat; ?></textarea>
					<small class="error-text"><?php echo form_error('alamat'); ?></small>
				</div>

				<div class="form-group">
					<label for="lattitude">Lattitude</label>
					<input type="text" class="form-control" name="lattitude" id="lattitude" placeholder="Lattitude"
						value="<?php echo $lattitude; ?>" />
					<small class="error-text"><?php echo form_error('lattitude'); ?></small>
				</div>

				<div class="form-group">
					<label for="longitude">Longitude</label>
					<input type="text" class="form-control" name="longitude" id="longitude" placeholder="Longitude"
						value="<?php echo $longitude; ?>" />
					<small class="error-text"><?php echo form_error('longitude'); ?></small>
				</div>

				<input type="hidden" name="id_unit_kerja" value="<?php echo $id_unit_kerja; ?>" />
				<div class="form-actions">
					<a href="<?php echo site_url('unit_kerja') ?>" class="btn-cancel">Batal</a>
					<button type="submit" class="btn-save"><?php echo $button ?></button>
				</div>
			</form>
		</div>
	</div>

	<!-- Modal Map -->
	<div id="mapModal" class="modal">
		<div class="modal-content">
			<span class="close" onclick="closeMapModal()">&times;</span>

			<div style="display: flex;">
				<input type="text" id="searchBox" placeholder="Cari lokasi..." />
				<button onclick="searchLocation()">Cari</button>
			</div>

			<div id="map"></div>

			<p id="selectedAddress">-</p>

			<div class="modal-footer">
				<button class="btn-cancel" onclick="closeMapModal()">Batal</button>
				<button class="btn-save" onclick="confirmLocation()">Simpan</button>
			</div>
		</div>
	</div>

	<footer class="footer">
		&copy;2025 BKPSDM Kota Malang
	</footer>
</body>
<script>
	let map, marker, selectedLatLng, selectedAddress;

	function openMapModal() {
		document.getElementById('mapModal').style.display = 'block';

		if (!map) {
			map = L.map('map').setView([-7.9797, 112.6304], 13);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '© OpenStreetMap'
			}).addTo(map);

			const redIcon = L.icon({
				iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
				shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
				iconSize: [25, 41],
				iconAnchor: [12, 41],
				popupAnchor: [1, -34],
				shadowSize: [41, 41]
			});

			marker = L.marker(map.getCenter(), { draggable: true, icon: redIcon }).addTo(map);

			marker.on('dragend', function (e) {
				selectedLatLng = e.target.getLatLng();
				fetchAddress(selectedLatLng.lat, selectedLatLng.lng);
			});

			map.on('click', function (e) {
				selectedLatLng = e.latlng;
				marker.setLatLng(e.latlng);
				fetchAddress(e.latlng.lat, e.latlng.lng);
			});
		}
	}

	function closeMapModal() {
		document.getElementById('mapModal').style.display = 'none';
	}

	function confirmLocation() {
		if (selectedAddress && selectedLatLng) {
			document.getElementById('alamat').value = selectedAddress;
			document.getElementById('lattitude').value = selectedLatLng.lat;
			document.getElementById('longitude').value = selectedLatLng.lng;
		}
		closeMapModal();
	}

	function searchLocation() {
		const query = document.getElementById('searchBox').value;
		if (!query) return;

		const provider = new window.GeoSearch.OpenStreetMapProvider();
		provider.search({ query }).then((results) => {
			if (results.length > 0) {
				const result = results[0];
				const latlng = [result.y, result.x];
				map.setView(latlng, 16);
				marker.setLatLng(latlng);
				selectedLatLng = { lat: result.y, lng: result.x };
				fetchAddress(result.y, result.x);
			}
		});
	}

	function fetchAddress(lat, lng) {
		fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
			.then(response => response.json())
			.then(data => {
				selectedAddress = data.display_name;
				document.getElementById('selectedAddress').innerText = data.display_name;
			});
	}

	document.getElementById('searchBox').addEventListener('keydown', function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			searchLocation();
		}
	});

</script>

</html>
