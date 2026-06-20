<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

include "koneksi/koneksi.php";

$alert = "";

/* =========================
   PROSES HAPUS FILE
========================= */
if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus']);

    $get = mysqli_query($koneksi, "SELECT dokumen FROM jurnal WHERE id_jurnal=$id");
    $data = mysqli_fetch_assoc($get);

    if ($data && !empty($data['dokumen'])) {

        $path = "uploads/" . $data['dokumen'];

        if (file_exists($path)) {
            unlink($path);
        }

        mysqli_query($koneksi, "UPDATE jurnal SET dokumen=NULL WHERE id_jurnal=$id");

        $alert = "<div class='alert alert-success alert-dismissible fade show'>
                    Dokumen berhasil dihapus
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
    }
}

/* =========================
   AMBIL DATA DOKUMEN
========================= */
$query = mysqli_query($koneksi, "
    SELECT * FROM jurnal 
    WHERE dokumen IS NOT NULL 
    AND dokumen != '' 
    ORDER BY tanggal DESC
");

$total_dokumen = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dokumen — LAPORIN</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:#f6f8fb;
    padding-top:90px;
    font-size:14px;
}
.navbar{
    background:#ffffff;
    box-shadow:0 4px 20px rgba(0,0,0,0.04);
}
.navbar-brand{
    font-weight:600;
}
.card{
    border:none;
    border-radius:18px;
    box-shadow:0 4px 25px rgba(0,0,0,0.05);
}
.section-title{
    font-weight:600;
    font-size:16px;
}
.badge-soft{
    background:#eef2ff;
    color:#0d6efd;
    font-weight:500;
}
.table thead{
    font-size:13px;
    text-transform:uppercase;
}
.file-name{
    max-width:220px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.btn-soft{
    background:#f1f3f7;
    border:none;
}
.btn-soft:hover{
    background:#e2e6ef;
}
.empty-state{
    padding:60px 0;
    color:#adb5bd;
}
.small-muted{
    font-size:13px;
    color:#6c757d;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="bi bi-journal-text me-1"></i> Laporin
    </a>

    <div class="ms-auto d-flex align-items-center">
        <a href="index.php" class="btn btn-light btn-sm me-3 rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>

        <span class="me-3 small-muted">
            <i class="bi bi-person-circle me-1"></i> <?= $_SESSION['login']; ?>
        </span>

        <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-3">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
  </div>
</nav>

<div class="container">

    <?= $alert ?>

    <!-- HEADER CARD -->
    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="section-title mb-1">
                    <i class="bi bi-folder2-open me-2"></i>
                    Arsip Dokumen Jurnal
                </h5>
                <div class="small-muted">
                    Semua bukti transaksi yang terupload pada jurnal
                </div>
            </div>

            <span class="badge badge-soft px-3 py-2">
                <?= $total_dokumen ?> Dokumen
            </span>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card p-4">

        <?php if($total_dokumen > 0) { ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="120">Tanggal</th>
                        <th>Keterangan</th>
                        <th width="120">No Bukti</th>
                        <th width="250">Dokumen</th>
                        <th width="120" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php while($row = mysqli_fetch_assoc($query)) { ?>
                    <tr>
                        <td><?= date("d M Y", strtotime($row['tanggal'])); ?></td>

                        <td>
                            <strong><?= $row['keterangan']; ?></strong>
                        </td>

                        <td><?= $row['no_bukti'] ?: '-'; ?></td>

                        <td>
                            <a href="uploads/<?= $row['dokumen']; ?>" 
                               target="_blank"
                               class="text-decoration-none file-name">
                               <?= $row['dokumen']; ?>
                            </a>
                        </td>

                        <td class="text-center">

                            <a href="uploads/<?= $row['dokumen']; ?>" 
                               class="btn btn-success btn-sm rounded-3"
                               target="_blank">
                               <i class="bi bi-download"></i>
                            </a>

                            <a href="?hapus=<?= $row['id_jurnal']; ?>" 
                               class="btn btn-danger btn-sm rounded-3"
                               onclick="return confirm('Hapus dokumen ini?')">
                               <i class="bi bi-trash"></i>
                            </a>

                        </td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>
        </div>

        <?php } else { ?>

        <div class="text-center empty-state">
            <i class="bi bi-folder-x" style="font-size:48px;"></i>
            <div class="mt-3">Belum ada dokumen yang diupload</div>
        </div>

        <?php } ?>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>