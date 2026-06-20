<?php
session_start();
include "koneksi/koneksi.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

/* Ambil akun */
$akun = mysqli_query($koneksi, "SELECT * FROM tb_master_akun ORDER BY id_akun");

/* ================= SIMPAN ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tanggal     = $_POST['tanggal'] ?? '';
    $keterangan  = trim($_POST['keterangan'] ?? '');
    $nominal     = $_POST['nominal'] ?? 0;
    $mata_uang   = $_POST['mata_uang'] ?? 'IDR';

    $id_akun = $_POST['akun'] ?? [];
    $debit   = $_POST['debit'] ?? [];
    $kredit  = $_POST['kredit'] ?? [];

    $total_debit  = array_sum($debit);
    $total_kredit = array_sum($kredit);

    /* ================= UPLOAD DOKUMEN ================= */
    $nama_file = null;

    if (!empty($_FILES['dokumen']['name'])) {

        $allowed = ['pdf','jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['dokumen']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Format file tidak diizinkan! (pdf, jpg, png)";
        } else {

            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $nama_file = time() . "_" . basename($_FILES["dokumen"]["name"]);
            move_uploaded_file($_FILES["dokumen"]["tmp_name"], $upload_dir . $nama_file);
        }
    }

    /* ================= VALIDASI ================= */
    if (!$error) {

        if ($total_debit != $total_kredit) {

            $error = "Total Debit dan Kredit harus sama!";

        } else {

            mysqli_begin_transaction($koneksi);

            try {

                /* Insert Header Jurnal */
                $stmt = mysqli_prepare($koneksi,
                    "INSERT INTO jurnal 
                    (tanggal, keterangan, nominal, mata_uang, dokumen) 
                    VALUES (?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    'ssdss',
                    $tanggal,
                    $keterangan,
                    $nominal,
                    $mata_uang,
                    $nama_file
                );

                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_errno($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                $id_jurnal = mysqli_insert_id($koneksi);

                /* Insert Detail */
                for ($i = 0; $i < count($id_akun); $i++) {

                    if (!empty($id_akun[$i]) && ($debit[$i] > 0 || $kredit[$i] > 0)) {

                        $stmt2 = mysqli_prepare($koneksi,
                            "INSERT INTO jurnal_detail 
                            (id_jurnal, id_akun, debit, kredit) 
                            VALUES (?, ?, ?, ?)"
                        );

                        mysqli_stmt_bind_param(
                            $stmt2,
                            'iidd',
                            $id_jurnal,
                            $id_akun[$i],
                            $debit[$i],
                            $kredit[$i]
                        );

                        mysqli_stmt_execute($stmt2);

                        if (mysqli_stmt_errno($stmt2)) {
                            throw new Exception(mysqli_stmt_error($stmt2));
                        }
                    }
                }

                mysqli_commit($koneksi);
                $success = "Transaksi berhasil disimpan!";

            } catch (Exception $e) {

                mysqli_rollback($koneksi);
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Transaksi — LAPORIN</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background-color: #f6f8fb;
    padding-top: 80px;
    font-size: 14px;
}
.navbar {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
}
.card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
}
.section-title {
    font-weight: 600;
    font-size: 16px;
}
.form-control, .form-select {
    border-radius: 10px;
}
.table thead th {
    background-color: #f9fafb;
    font-size: 13px;
}
</style>
</head>
<body>

<nav class="navbar fixed-top">
<div class="container d-flex justify-content-between">
    <span class="navbar-brand">
        <i class="bi bi-journal-text me-1"></i> Laporin
    </span>
    <a href="index.php" class="btn btn-light btn-sm rounded-3">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
</nav>

<div class="container">

<div class="card p-4">

<h5 class="section-title mb-3">Input Jurnal Umum</h5>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<div class="row mb-3">
    <div class="col-md-3">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label>Keterangan</label>
        <input type="text" name="keterangan" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label>Dokumen</label>
        <input type="file" name="dokumen" class="form-control">
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <label>Nominal</label>
        <input type="number" step="0.01" name="nominal" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label>Mata Uang</label>
        <select name="mata_uang" class="form-select" required>
            <option value="IDR">IDR (Rupiah)</option>
            <option value="USD">USD (Dollar)</option>
            <option value="EUR">EUR (Euro)</option>
            <option value="JPY">JPY (Yen)</option>
        </select>
    </div>
</div>

<table class="table align-middle">
<thead>
<tr>
<th>Akun</th>
<th>Debit</th>
<th>Kredit</th>
</tr>
</thead>
<tbody>

<?php for($i=0;$i<5;$i++): ?>
<tr>
<td>
<select name="akun[]" class="form-select">
<option value="">-- Pilih Akun --</option>
<?php
mysqli_data_seek($akun,0);
while($a=mysqli_fetch_assoc($akun)):
?>
<option value="<?= $a['id_akun'] ?>">
<?= $a['id_akun'] ?> - <?= $a['nama_akun'] ?>
</option>
<?php endwhile; ?>
</select>
</td>
<td><input type="number" step="0.01" name="debit[]" class="form-control"></td>
<td><input type="number" step="0.01" name="kredit[]" class="form-control"></td>
</tr>
<?php endfor; ?>

</tbody>
</table>

<div class="d-grid">
<button type="submit" class="btn btn-primary rounded-3">
<i class="bi bi-save me-1"></i> Simpan Transaksi
</button>
</div>

</form>

</div>
</div>

</body>
</html>