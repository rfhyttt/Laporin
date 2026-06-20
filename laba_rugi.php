<?php
$tanggal_awal  = $_GET['awal']  ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');

function format_rp($angka){
    return number_format($angka ?? 0,0,',','.');
}

/* =======================
   PENDAPATAN
======================= */
$qPendapatan = mysqli_query($koneksi,"
    SELECT 
        a.kode_akun,
        a.nama_akun,
        SUM(jd.kredit - jd.debit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='pendapatan'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    GROUP BY a.id_akun
");

$total_pendapatan = 0;

/* =======================
   BEBAN
======================= */
$qBeban = mysqli_query($koneksi,"
    SELECT 
        a.kode_akun,
        a.nama_akun,
        SUM(jd.debit - jd.kredit) AS total
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='beban'
    AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    GROUP BY a.id_akun
");

$total_beban = 0;
?>

<h5 class="mb-3">Laporan Laba Rugi</h5>

<form method="GET" class="row mb-4">
    <input type="hidden" name="menu" value="laba_rugi">

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

<div class="table-responsive">
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>Keterangan</th>
            <th class="text-end">Jumlah</th>
        </tr>
    </thead>
    <tbody>

        <!-- PENDAPATAN -->
        <tr>
            <td colspan="2"><strong>PENDAPATAN</strong></td>
        </tr>

        <?php while($row = mysqli_fetch_assoc($qPendapatan)): 
            $total_pendapatan += $row['total'];
        ?>
        <tr>
            <td>
                <?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?>
            </td>
            <td class="text-end">
                <?= format_rp($row['total']) ?>
            </td>
        </tr>
        <?php endwhile; ?>

        <tr class="table-light">
            <td><strong>Total Pendapatan</strong></td>
            <td class="text-end fw-bold">
                <?= format_rp($total_pendapatan) ?>
            </td>
        </tr>

        <!-- BEBAN -->
        <tr>
            <td colspan="2" class="pt-4"><strong>BEBAN</strong></td>
        </tr>

        <?php while($row = mysqli_fetch_assoc($qBeban)): 
            $total_beban += $row['total'];
        ?>
        <tr>
            <td>
                <?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?>
            </td>
            <td class="text-end">
                <?= format_rp($row['total']) ?>
            </td>
        </tr>
        <?php endwhile; ?>

        <tr class="table-light">
            <td><strong>Total Beban</strong></td>
            <td class="text-end fw-bold">
                <?= format_rp($total_beban) ?>
            </td>
        </tr>

        <!-- LABA RUGI -->
        <?php 
        $laba_rugi = $total_pendapatan - $total_beban;
        ?>

        <tr class="table-primary">
            <td><strong>Laba / Rugi Bersih</strong></td>
            <td class="text-end fw-bold">
                <?= format_rp($laba_rugi) ?>
            </td>
        </tr>

    </tbody>
</table>
</div>

<a href="export_laporan.php?menu=laba_rugi"
   class="btn btn-dark btn-sm">
   <i class="bi bi-printer"></i> Print / PDF
</a>