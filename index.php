<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

include "koneksi/koneksi.php";

$tanggal_awal  = $_GET['awal']  ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');

function format_rp($angka){
    return 'Rp ' . number_format($angka ?? 0,0,',','.');
}

/* ================= TOTAL PENDAPATAN ================= */
$qPendapatan = mysqli_query($koneksi,"
    SELECT SUM(jd.kredit) AS jumlah
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='pendapatan'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$total_pendapatan=mysqli_fetch_assoc($qPendapatan)['jumlah'] ?? 0;

/* ================= TOTAL BEBAN ================= */
$qBeban=mysqli_query($koneksi,"
    SELECT SUM(jd.debit) AS jumlah
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='beban'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$total_beban=mysqli_fetch_assoc($qBeban)['jumlah'] ?? 0;

$laba_rugi=$total_pendapatan-$total_beban;

/* ================= DATA GRAFIK LABA ================= */
$qGrafik = mysqli_query($koneksi,"
    SELECT 
        DATE_FORMAT(j.tanggal,'%Y-%m') as bulan,
        SUM(CASE WHEN LOWER(a.tipe)='pendapatan' THEN jd.kredit ELSE 0 END) -
        SUM(CASE WHEN LOWER(a.tipe)='beban' THEN jd.debit ELSE 0 END) 
        AS laba
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    GROUP BY DATE_FORMAT(j.tanggal,'%Y-%m')
    ORDER BY bulan ASC
");

$bulan = [];
$data_laba = [];

while($rowGrafik = mysqli_fetch_assoc($qGrafik)){
    $bulan[] = $rowGrafik['bulan'];
    $data_laba[] = (float)$rowGrafik['laba'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — LAPORIN</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background:#f4f6fb;
    padding-top:85px;
    font-family:'Segoe UI',sans-serif;
    font-size:14px;
}

.navbar{
    background:#fff;
    box-shadow:0 4px 25px rgba(0,0,0,0.05);
}

.navbar-brand{
    font-weight:600;
    font-size:18px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 30px rgba(0,0,0,0.04);
}

.welcome-card{
    background:linear-gradient(135deg,#ffffff,#f8fbff);
}

.summary-card{
    padding:20px;
    border-radius:16px;
    color:#fff;
}

.bg-income{background:linear-gradient(135deg,#198754,#28c76f);}
.bg-expense{background:linear-gradient(135deg,#dc3545,#ff6b6b);}
.bg-profit{background:linear-gradient(135deg,#0d6efd,#4f8dfd);}

.summary-title{
    font-size:13px;
    opacity:0.9;
}

.summary-value{
    font-size:20px;
    font-weight:600;
}

.section-title{
    font-weight:600;
    font-size:16px;
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

    <a class="navbar-brand" href="#">
      <i class="bi bi-journal-text me-1"></i> Laporin
    </a>

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-4">
            <li class="nav-item">
                <a class="nav-link" href="akun.php">
                    <i class="bi bi-wallet2 me-1"></i> Akun
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="transaksi.php">
                    <i class="bi bi-arrow-left-right me-1"></i> Transaksi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="keuangan.php">
                    <i class="bi bi-bar-chart-line me-1"></i> Keuangan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="dokumen.php">
                    <i class="bi bi-folder2-open me-1"></i> Dokumen
                </a>
            </li>
        </ul>

        <div class="ms-auto d-flex align-items-center">
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

<div class="container">

    <!-- WELCOME -->
    <div class="card welcome-card p-4 mb-4">
        <h5 class="section-title mb-3">Selamat Datang di LAPORIN by UNRI</h5>
        <p class="small-muted mb-2">
            Aplikasi Akuntansi yang dikembangkan oleh Mahasiswa Akuntansi Angkatan 2022
        </p>
        <p class="mb-2 fw-semibold">
            Rifky Hidayat Aulia Muhti<br>
            Kholifah Taufiq Rifai<br>
            Eka Kurnia Sandi<br>
            Faddli Setiawan
        </p>
        <p class="small-muted mb-0">
            Konsentrasi Sistem Informasi, pada Matakuliah Database Manajemen Sistem dengan dosen pengampu 
            <strong>Dr. Ruhul Fitrios, SE., M.Si., Ak., CA., BKP.</strong>
        </p>
    </div>

    <!-- SUMMARY -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="summary-card bg-income">
                <div class="summary-title">Total Pendapatan</div>
                <div class="summary-value"><?= format_rp($total_pendapatan) ?></div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="summary-card bg-expense">
                <div class="summary-title">Total Beban</div>
                <div class="summary-value"><?= format_rp($total_beban) ?></div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="summary-card bg-profit">
                <div class="summary-title">Laba / Rugi Bersih</div>
                <div class="summary-value"><?= format_rp($laba_rugi) ?></div>
            </div>
        </div>
    </div>

    <!-- GRAFIK -->
    <div class="card p-4 mb-4">
        <h5 class="section-title mb-3">
            <i class="bi bi-graph-up-arrow me-2"></i>
            Grafik Perkembangan Laba
        </h5>
        <canvas id="grafikLaba" height="100"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('grafikLaba'), {
    type: 'line',
    data: {
        labels: <?= json_encode($bulan); ?>,
        datasets: [{
            label: 'Laba Bersih',
            data: <?= json_encode($data_laba); ?>,
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(13,110,253,0.08)',
            borderColor: '#0d6efd'
        }]
    }
});
</script>

</body>
</html>