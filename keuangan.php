<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

include "koneksi/koneksi.php";

$menu = $_GET['menu'] ?? 'jurnal';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keuangan — LAPORIN</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: #f4f6fb;
    padding-top: 85px;
    font-size: 14px;
    font-family: 'Segoe UI', sans-serif;
}

/* NAVBAR */
.navbar {
    background: #ffffff;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    padding: 12px 0;
}

.navbar-brand {
    font-weight: 600;
    font-size: 18px;
    letter-spacing: 0.3px;
}

/* CARD */
.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
}

.section-title {
    font-weight: 600;
    font-size: 16px;
}

/* SIDEBAR */
.menu-keuangan .list-group-item {
    border: none;
    border-radius: 12px;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
    padding: 10px 14px;
    transition: all 0.2s ease;
    color: #495057;
}

.menu-keuangan .list-group-item i {
    font-size: 15px;
}

.menu-keuangan .list-group-item:hover {
    background: #eef2ff;
    color: #0d6efd;
    transform: translateX(3px);
}

.menu-keuangan .active {
    background: linear-gradient(135deg, #0d6efd, #4f8dfd) !important;
    color: #fff !important;
    box-shadow: 0 6px 18px rgba(13,110,253,0.3);
}

/* BUTTON GHOST */
.btn-ghost {
    text-decoration: none;
    padding: 7px 16px;
    border-radius: 12px;
    font-size: 13px;
    background: #f1f3f7;
    color: #333;
    margin-right: 15px;
    transition: all 0.2s ease;
}

.btn-ghost:hover {
    background: #e2e6ef;
    color: #000;
}

/* TEXT SMALL */
.small-muted {
    font-size: 13px;
    color: #6c757d;
}

/* CONTENT AREA */
.content-area {
    min-height: 520px;
}

/* Responsive */
@media(max-width: 768px){
    .menu-keuangan {
        margin-bottom: 20px;
    }
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

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">

        <div class="ms-auto d-flex align-items-center">
            <a href="index.php" class="btn-ghost">
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
  </div>
</nav>

<!-- CONTENT -->
<div class="container">

    <!-- HEADER -->
    <div class="card p-4 mb-4">
        <h5 class="section-title mb-1">
            <i class="bi bi-bar-chart-line me-2"></i>
            Menu Laporan Keuangan
        </h5>
        <p class="small-muted mb-0">
            Pilih laporan untuk melihat detail keuangan perusahaan.
        </p>
    </div>

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3">
            <div class="card p-3 menu-keuangan">
                <div class="list-group">

                    <a href="?menu=jurnal"
                       class="list-group-item <?= $menu=='jurnal'?'active':'' ?>">
                       <i class="bi bi-journal-text me-2"></i> Jurnal Umum
                    </a>

                    <a href="?menu=buku_besar"
                       class="list-group-item <?= $menu=='buku_besar'?'active':'' ?>">
                       <i class="bi bi-book me-2"></i> Buku Besar
                    </a>

                    <a href="?menu=laba_rugi"
                       class="list-group-item <?= $menu=='laba_rugi'?'active':'' ?>">
                       <i class="bi bi-graph-up me-2"></i> Laporan Laba Rugi
                    </a>

                    <a href="?menu=ekuitas"
                       class="list-group-item <?= $menu=='ekuitas'?'active':'' ?>">
                       <i class="bi bi-diagram-3 me-2"></i> Perubahan Ekuitas
                    </a>

                    <a href="?menu=neraca"
                       class="list-group-item <?= $menu=='neraca'?'active':'' ?>">
                       <i class="bi bi-balance-scale me-2"></i> Posisi Keuangan
                    </a>

                    <a href="?menu=arus_kas"
                       class="list-group-item <?= $menu=='arus_kas'?'active':'' ?>">
                       <i class="bi bi-cash-stack me-2"></i> Arus Kas
                    </a>

                </div>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="col-md-9">
            <div class="card p-4 content-area">

                <?php
                switch($menu){
                    case 'buku_besar':
                        include "keuangan/buku_besar.php";
                        break;
                    case 'laba_rugi':
                        include "keuangan/laba_rugi.php";
                        break;
                    case 'ekuitas':
                        include "keuangan/perubahan_ekuitas.php";
                        break;
                    case 'neraca':
                        include "keuangan/posisi_keuangan.php";
                        break;
                    case 'arus_kas':
                        include "keuangan/arus_kas.php";
                        break;
                    case 'calk':
                        include "keuangan/calk.php";
                        break;
                    default:
                        include "keuangan/jurnal_umum.php";
                }
                ?>

            </div>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>