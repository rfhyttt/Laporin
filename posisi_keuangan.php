<?php
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');

function format_rp($angka){
    return number_format($angka ?? 0,0,',','.');
}

/* =========================
   ASET
========================= */
$qAset = mysqli_query($koneksi,"
    SELECT 
        a.kode_akun,
        a.nama_akun,
        SUM(jd.debit - jd.kredit) AS saldo
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='aset'
    AND j.tanggal <= '$tanggal_akhir'
    GROUP BY a.id_akun
");

$total_aset = 0;

/* =========================
   LIABILITAS
========================= */
$qLiabilitas = mysqli_query($koneksi,"
    SELECT 
        a.kode_akun,
        a.nama_akun,
        SUM(jd.kredit - jd.debit) AS saldo
    FROM jurnal_detail jd
    JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun=a.id_akun
    WHERE LOWER(a.tipe)='liabilitas'
    AND j.tanggal <= '$tanggal_akhir'
    GROUP BY a.id_akun
");

$total_liabilitas = 0;


/* =====================================================
   HITUNG EKUITAS (SAMA DENGAN PERUBAHAN EKUITAS)
===================================================== */

$tanggal_awal = date('Y-01-01', strtotime($tanggal_akhir));

/* Modal Awal */
$qModal = mysqli_query($koneksi,"
SELECT SUM(jd.kredit - jd.debit) AS saldo
FROM jurnal_detail jd
JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
JOIN tb_master_akun a ON jd.id_akun=a.id_akun
WHERE LOWER(a.tipe)='ekuitas'
AND j.tanggal < '$tanggal_akhir'
");
$modal_awal = mysqli_fetch_assoc($qModal)['saldo'] ?? 0;


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


/* Prive */
$qPrive = mysqli_query($koneksi,"
SELECT SUM(jd.debit - jd.kredit) AS total
FROM jurnal_detail jd
JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
JOIN tb_master_akun a ON jd.id_akun=a.id_akun
WHERE LOWER(a.nama_akun) LIKE '%prive%'
AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
");
$prive = mysqli_fetch_assoc($qPrive)['total'] ?? 0;


/* Ekuitas Akhir */
$ekuitas_akhir = $modal_awal + $laba_bersih - $prive;

$total_ekuitas = $ekuitas_akhir;
?>

<h5 class="mb-3">Laporan Posisi Keuangan (Neraca)</h5>

<form method="GET" class="row mb-4">
    <input type="hidden" name="menu" value="neraca">

    <div class="col-md-10">
        <label class="form-label">Per Tanggal</label>
        <input type="date" name="akhir" value="<?= $tanggal_akhir ?>" class="form-control">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Tampilkan</button>
    </div>
</form>

<div class="row">

    <!-- ASET -->
    <div class="col-md-6">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th colspan="2">ASET</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row=mysqli_fetch_assoc($qAset)):
                $total_aset += $row['saldo'];
            ?>
                <tr>
                    <td><?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?></td>
                    <td class="text-end"><?= format_rp($row['saldo']) ?></td>
                </tr>
            <?php endwhile; ?>

                <tr class="table-light">
                    <td><strong>Total Aset</strong></td>
                    <td class="text-end fw-bold"><?= format_rp($total_aset) ?></td>
                </tr>

            </tbody>
        </table>
    </div>

    <!-- LIABILITAS + EKUITAS -->
    <div class="col-md-6">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th colspan="2">LIABILITAS</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row=mysqli_fetch_assoc($qLiabilitas)):
                $total_liabilitas += $row['saldo'];
            ?>
                <tr>
                    <td><?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?></td>
                    <td class="text-end"><?= format_rp($row['saldo']) ?></td>
                </tr>
            <?php endwhile; ?>

                <tr class="table-light">
                    <td><strong>Total Liabilitas</strong></td>
                    <td class="text-end fw-bold"><?= format_rp($total_liabilitas) ?></td>
                </tr>

                <tr>
                    <td colspan="2" class="pt-4"><strong>EKUITAS</strong></td>
                </tr>

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

                <tr class="table-light">
                    <td><strong>Total Ekuitas</strong></td>
                    <td class="text-end fw-bold"><?= format_rp($total_ekuitas) ?></td>
                </tr>

                <tr class="table-primary">
                    <td><strong>Total Liabilitas + Ekuitas</strong></td>
                    <td class="text-end fw-bold">
                        <?= format_rp($total_liabilitas + $total_ekuitas) ?>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

</div>

<a href="export_laporan.php?menu=posisi_keuangan&akhir=<?= $tanggal_akhir ?>"
   class="btn btn-dark btn-sm">
   <i class="bi bi-printer"></i> Print / PDF
</a>