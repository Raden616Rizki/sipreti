<!doctype html>
<html>

<head>
	<title>Daftar Log Absensi</title>
	<link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>" />
	<style>
		body {
			padding: 15px;
		}
	</style>
</head>

<body>
	<h2 style="margin-top:0px">Daftar Log Absensi</h2>
	<div class="row" style="margin-bottom: 10px">
		<div class="col-md-4">
			<?php echo anchor(site_url('log_absensi/create'), 'Create', 'class="btn btn-primary"'); ?>
		</div>
		<div class="col-md-4 text-center">
			<div style="margin-top: 8px" id="message">
				<?php echo $this->session->userdata('message') <> '' ? $this->session->userdata('message') : ''; ?>
			</div>
		</div>
		<div class="col-md-1 text-right">
		</div>
		<div class="col-md-3 text-right">
			<form action="<?php echo site_url('log_absensi/index'); ?>" class="form-inline" method="get">
				<div class="input-group">
					<input type="text" class="form-control" name="q" value="<?php echo $q; ?>">
					<span class="input-group-btn">
						<?php
						if ($q <> '') {
							?>
							<a href="<?php echo site_url('log_absensi'); ?>" class="btn btn-default">Reset</a>
							<?php
						}
						?>
						<button class="btn btn-primary" type="submit">Search</button>
					</span>
				</div>
			</form>
		</div>
	</div>
	<table class="table table-bordered" style="margin-bottom: 10px">
		<tr>
			<th>No</th>
			<th>Id Pegawai</th>
			<th>Jenis Absensi</th>
			<th>Check Mode</th>
			<th>Waktu Absensi</th>
			<th>Lattitude</th>
			<th>Longitude</th>
			<th>Nama Lokasi</th>
			<th>Foto Presensi</th>
			<th>Dokumen</th>
			<th>Waktu Verifikasi</th>
			<th>Action</th>
		</tr><?php
		foreach ($log_absensi_data as $log_absensi) {
			?>
			<tr>
				<td width="80px"><?php echo ++$start ?></td>
				<td><?php echo $log_absensi->id_pegawai ?></td>
				<td><?php echo $log_absensi->jenis_absensi == 1 ? 'DD/DL' : 'Reguler'; ?></td>
				<td><?php echo $log_absensi->check_mode == 1 ? 'Check Out' : 'Check In'; ?></td>
				<td><?php echo $log_absensi->waktu_absensi ?></td>
				<td><?php echo $log_absensi->lattitude ?></td>
				<td><?php echo $log_absensi->longitude ?></td>
				<td><?php echo $log_absensi->nama_lokasi ?></td>
				<td>
					<?php
					$foto_path = FCPATH . 'uploads/presensi/' . $log_absensi->id_pegawai . '/' . $log_absensi->url_foto_presensi;
					if (!empty($log_absensi->url_foto_presensi) && file_exists($foto_path)) {
						$foto_url = base_url('uploads/presensi/' . $log_absensi->id_pegawai . '/' . $log_absensi->url_foto_presensi);
						echo '<img src="' . $foto_url . '" alt="Foto Presensi" width="128" style="object-fit: cover;">';
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

				<td><?php echo $log_absensi->waktu_verifikasi ?></td>

				<td style="text-align:center" width="200px">
					<?php
					echo anchor(site_url('log_absensi/read/' . $log_absensi->id_log_absensi), 'Read');
					echo ' | ';
					echo anchor(site_url('log_absensi/update/' . $log_absensi->id_log_absensi), 'Update');
					echo ' | ';
					echo anchor(site_url('log_absensi/delete/' . $log_absensi->id_log_absensi), 'Delete', 'onclick="javasciprt: return confirm(\'Are You Sure ?\')"');
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</table>
	<div class="row">
		<div class="col-md-6">
			<a href="#" class="btn btn-primary">Total Record : <?php echo $total_rows ?></a>
		</div>
		<div class="col-md-6 text-right">
			<?php echo $pagination ?>
		</div>
	</div>
</body>

</html>
