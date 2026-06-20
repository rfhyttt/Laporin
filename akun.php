<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}
include "koneksi/koneksi.php";

/* ===== PROSES SIMPAN ===== */
if(isset($_POST['simpan'])){
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_akun']);
    $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);

    if(!empty($nama) && !empty($tipe)){
        mysqli_query($koneksi, "INSERT INTO tb_master_akun (nama_akun, tipe)
                                VALUES ('$nama','$tipe')");
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f6f8fb;
    padding-top: 80px;
    font-size: 14px;
}

/* Navbar */
.navbar {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}

.navbar-brand {
    font-weight: 600;
    font-size: 18px;
}

/* Card */
.card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
}

.section-title {
    font-weight: 600;
    font-size: 16px;
}

/* Table */
.table thead th {
    font-weight: 600;
    font-size: 13px;
    background-color: #f9fafb;
    border-bottom: 1px solid #eef1f5;
}

.table td {
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8f9fc;
}

/* Badge */
.badge-custom {
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 500;
}

.badge-aset {
    background-color: #e7f1ff;
    color: #0d6efd;
}

.badge-kewajiban {
    background-color: #fde8e8;
    color: #dc3545;
}

/* Button Action */
.btn-action {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

/* Ghost Button */
.btn-ghost {
    text-decoration: none;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 13px;
    background-color: #f1f3f7;
    color: #333;
    margin-right: 15px;
    transition: 0.2s;
}

.btn-ghost:hover {
    background-color: #e2e6ef;
    color: #000;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand" href="#">
      <i class="bi bi-journal-text me-1"></i> Laporin
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto ms-4">
        <li class="nav-item">
          <a class="nav-link active" href="#"></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"></a>
        </li>
      </ul>

      <div class="d-flex align-items-center">
        <a href="index.php" class="btn-ghost">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>

        <span class="me-3 text-muted">
          <i class="bi bi-person-circle me-1"></i> rifky2103
        </span>
      </div>
    </div>
  </div>
</nav>

<!-- CONTENT -->
<div class="container">

    <!-- TAMBAH AKUN -->
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="section-title mb-0">Tambah Akun Baru</h5>
        </div>

        <form method="POST" class="row g-3">
    <div class="col-md-5">
        <label class="form-label">Nama Akun</label>
        <input type="text" name="nama_akun" class="form-control rounded-3" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Jenis Akun</label>
        <select name="tipe" class="form-select rounded-3" required>
            <option value="">Pilih Jenis</option>
            <option value="aset">Aset</option>
            <option value="kewajiban">Kewajiban</option>
            <option value="ekuitas">Ekuitas</option>
            <option value="pendapatan">Pendapatan</option>
            <option value="beban">Beban</option>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button type="submit" name="simpan" class="btn btn-primary w-100 rounded-3">
            <i class="bi bi-save me-1"></i> Simpan
        </button>
    </div>
</form>
    </div>

    <!-- DAFTAR AKUN -->
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="section-title mb-0">Daftar Akun</div>

            <select id="filterTipe" class="form-select filter-box" onchange="filterTabel()">
                <option value="all">Semua Jenis</option>
                <option value="aset">Aset</option>
                <option value="kewajiban">Kewajiban</option>
                <option value="ekuitas">Ekuitas</option>
                <option value="pendapatan">Pendapatan</option>
                <option value="beban">Beban</option>
            </select>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th width="70">No</th>
                        <th>Nama Akun</th>
                        <th width="200">Jenis</th>
                        <th width="170" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $q = mysqli_query($koneksi, "SELECT * FROM tb_master_akun ORDER BY nama_akun ASC");
                while ($d = mysqli_fetch_assoc($q)):
                ?>
                <tr data-jenis="<?= strtolower($d['tipe']); ?>">
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($d['nama_akun']); ?></td>
                    <td>
                        <span class="badge-jenis <?= strtolower($d['tipe']); ?>">
                            <?= ucfirst($d['tipe']); ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="edit_master.php?id_akun=<?= $d['id_akun']; ?>" class="btn btn-warning btn-sm me-1">Edit</a>
                        <a href="proses/proses.php?proses=hapus&id_akun=<?= $d['id_akun']; ?>"
                           onclick="return confirm('Hapus akun ini?')"
                           class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>