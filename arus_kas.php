<?php

$tanggal_awal  = $_GET['awal']  ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');

function format_rp($angka){
    return number_format($angka ?? 0,0,',','.');
}

/* =========================
   LABA BERSIH
========================= */

/* Pendapatan */
$pendapatan = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT SUM(jd.kredit - jd.debit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='pendapatan'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
"))['total'] ?? 0;

/* Beban */
$beban = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT SUM(jd.debit - jd.kredit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='beban'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
"))['total'] ?? 0;

$laba_bersih = $pendapatan - $beban;

/* =========================
   KAS AWAL
========================= */
$kas_awal = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT SUM(jd.debit - jd.kredit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.nama_akun) LIKE '%kas%'
    AND j.tanggal < '$tanggal_awal'
"))['total'] ?? 0;

/* =========================
   KAS AKHIR
========================= */
$kas_akhir = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT SUM(jd.debit - jd.kredit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.nama_akun) LIKE '%kas%'
    AND j.tanggal <= '$tanggal_akhir'
"))['total'] ?? 0;

$kenaikan_kas = $kas_akhir - $kas_awal;
?>


<h5 class="mb-3">Laporan Arus Kas</h5>

<form method="GET" class="row mb-4">
    <input type="hidden" name="menu" value="arus_kas">

    <div class="col-md-5">
        <label class="form-label">Tanggal Awal</label>
        <input type="date" name="awal" value="<?= $tanggal_awal ?>" class="form-control">
    </div>

    <div class="col-md-5">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" name="akhir" value="<?= $tanggal_akhir ?>" class="form-control">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Tampilkan</button>
    </div>
</form>

<table class="table table-bordered">
    <tbody>

        <tr>
            <td>Kas Awal</td>
            <td class="text-end"><?= format_rp($kas_awal) ?></td>
        </tr>

        <tr>
            <td>Laba Bersih</td>
            <td class="text-end"><?= format_rp($laba_bersih) ?></td>
        </tr>

        <tr>
            <td>Kenaikan / Penurunan Kas</td>
            <td class="text-end"><?= format_rp($kenaikan_kas) ?></td>
        </tr>

        <tr class="table-primary">
            <td><strong>Kas Akhir</strong></td>
            <td class="text-end fw-bold"><?= format_rp($kas_akhir) ?></td>
        </tr>

    </tbody>
</table>

<a href="export_laporan.php?menu=arus_kas"
   class="btn btn-dark btn-sm">
   <i class="bi bi-printer"></i> Print / PDF
</a>