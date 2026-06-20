<?php
$tanggal_awal  = $_GET['awal']  ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');

function format_rp($angka){
    return number_format($angka ?? 0,0,',','.');
}

/* =========================
   MODAL AWAL (EKUITAS)
========================= */
$qModal = mysqli_query($koneksi,"
    SELECT SUM(jd.kredit - jd.debit) AS saldo
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='ekuitas'
    AND j.tanggal < '$tanggal_akhir'
");
$rowModal = mysqli_fetch_assoc($qModal);
$modal_awal = $rowModal['saldo'] ?? 0;

/* =========================
   LABA BERSIH
========================= */

/* Pendapatan */
$qPendapatan = mysqli_query($koneksi,"
    SELECT SUM(jd.kredit - jd.debit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='pendapatan'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$total_pendapatan = mysqli_fetch_assoc($qPendapatan)['total'] ?? 0;

/* Beban */
$qBeban = mysqli_query($koneksi,"
    SELECT SUM(jd.debit - jd.kredit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='beban'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$total_beban = mysqli_fetch_assoc($qBeban)['total'] ?? 0;

$laba_bersih = $total_pendapatan - $total_beban;

/* =========================
   PRIVE (Jika Ada)
========================= */
$qPrive = mysqli_query($koneksi,"
    SELECT SUM(jd.debit - jd.kredit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.nama_akun) LIKE '%prive%'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$prive = mysqli_fetch_assoc($qPrive)['total'] ?? 0;

/* EKUITAS AKHIR */
$ekuitas_akhir = $modal_awal + $laba_bersih - $prive;
?>

<h5 class="mb-3">Laporan Perubahan Ekuitas</h5>

<form method="GET" class="row mb-4">
    <input type="hidden" name="menu" value="ekuitas">

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
            <td>Modal Awal</td>
            <td class="text-end"><?= format_rp($modal_awal) ?></td>
        </tr>

        <tr>
            <td>Laba Bersih</td>
            <td class="text-end"><?= format_rp($laba_bersih) ?></td>
        </tr>

        <tr>
            <td>Prive</td>
            <td class="text-end">(<?= format_rp($prive) ?>)</td>
        </tr>

        <tr class="table-primary">
            <td><strong>Ekuitas Akhir</strong></td>
            <td class="text-end fw-bold">
                <?= format_rp($ekuitas_akhir) ?>
            </td>
        </tr>
    </tbody>
</table>

<a href="export_laporan.php?menu=perubahan_ekuitas.php"
   class="btn btn-dark btn-sm">
   <i class="bi bi-printer"></i> Print / PDF
</a>