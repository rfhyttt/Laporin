<?php
$tanggal_awal  = $_GET['awal']  ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');

function format_rp($angka){
    return number_format($angka ?? 0,0,',','.');
}

$query = mysqli_query($koneksi,"
    SELECT 
        j.id_jurnal,
        j.tanggal,
        j.keterangan,
        a.kode_akun,
        a.nama_akun,
        jd.debit,
        jd.kredit
    FROM jurnal j
    JOIN jurnal_detail jd ON j.id_jurnal = jd.id_jurnal
    JOIN tb_master_akun a ON jd.id_akun = a.id_akun
    WHERE j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ORDER BY j.tanggal ASC, j.id_jurnal ASC
");
?>

<h5 class="mb-3">Jurnal Umum</h5>

<form method="GET" class="row mb-3">
    <input type="hidden" name="menu" value="jurnal">
    <div class="col-md-4">
        <label class="form-label">Tanggal Awal</label>
        <input type="date" name="awal" value="<?= $tanggal_awal ?>" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" name="akhir" value="<?= $tanggal_akhir ?>" class="form-control">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary w-100">Tampilkan</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Akun</th>
            <th class="text-end">Debit</th>
            <th class="text-end">Kredit</th>
        </tr>
    </thead>
    <tbody>

    <?php 
    $last_jurnal = null;
    while($row = mysqli_fetch_assoc($query)): 
    ?>
        <tr>
            <td><?= $row['tanggal'] ?></td>

            <td>
                <?php 
                if($last_jurnal != $row['id_jurnal']){
                    echo $row['keterangan'];
                    $last_jurnal = $row['id_jurnal'];
                }
                ?>
            </td>

            <td>
                <?= $row['kode_akun'] ?> - <?= $row['nama_akun'] ?>
            </td>

            <td class="text-end">
                <?= $row['debit'] > 0 ? format_rp($row['debit']) : '' ?>
            </td>

            <td class="text-end">
                <?= $row['kredit'] > 0 ? format_rp($row['kredit']) : '' ?>
            </td>
        </tr>
    <?php endwhile; ?>

    </tbody>
</table>
</div>