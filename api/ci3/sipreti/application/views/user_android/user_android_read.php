<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User Android</title>
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/images/sipreti_web_logo.png'); ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lobster&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/styles.css?v=<?= time();'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/form-styles.css?v=<?= time();'); ?>">
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
            <a class="active-sub" href="/sipreti/user_android">User Android</a>
            <a href="/sipreti/vektor_pegawai/list_pegawai">Biometrik Pegawai</a>
        </div>
    </div>

    <div class="card-container">
        <div class="card">
            <div>
                <h2 class="card-header">Detail User Android</h2>
            </div>

            <div class="form-group">
                <label>Nama Pegawai</label>
                <input type="text" class="form-control" value="<?php echo $nama_pegawai; ?>" readonly />
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" value="<?php echo $username; ?>" readonly />
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="text" class="form-control" value="<?php echo $email; ?>" readonly />
            </div>

            <div class="form-group">
                <label>No HP</label>
                <input type="text" class="form-control" value="<?php echo $no_hp; ?>" readonly />
            </div>

            <div class="form-group">
                <label>Valid HP</label>
                <input type="text" class="form-control" value="<?php echo $valid_hp; ?>" readonly />
            </div>

            <div class="form-actions">
                <a href="<?php echo site_url('user_android') ?>" class="btn-cancel">Kembali</a>
            </div>
        </div>
    </div>
</body>

</html>
