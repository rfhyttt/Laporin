<?php
$tanggal_awal  = $_GET['awal']  ?? date('Y-m-01');
$tanggal_akhir = $_GET['akhir'] ?? date('Y-m-t');
$id_akun       = $_GET['akun']  ?? '';

function format_rp($angka){
    return number_format($angka ?? 0,0,',','.');
}

/* Ambil daftar akun */
$akun = mysqli_query($koneksi,"SELECT * FROM tb_master_akun ORDER BY kode_akun");

/* Jika akun dipilih */
if($id_akun != ''){

    $akun_detail = mysqli_fetch_assoc(
        mysqli_query($koneksi,"SELECT * FROM tb_master_akun WHERE id_akun='$id_akun'")
    );

    $query = mysqli_query($koneksi,"
        SELECT j.tanggal, j.keterangan, jd.debit, jd.kredit
        FROM jurnal_detail jd
        JOIN jurnal j ON jd.id_jurnal=j.id_jurnal
        WHERE jd.id_akun='$id_akun'
        AND j.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
        ORDER BY j.tanggal ASC
    ");

    $saldo = 0;
}
?>

<h5 class="mb-3">Buku Besar</h5>

<form method="GET" class="row mb-3">
    <input type="hidden" name="menu" value="buku_besar">

    <div class="col-md-4">
        <label class="form-label">Pilih Akun</label>
        <select name="akun" class="form-select" required>
            <option value="">-- Pilih Akun --</option>
            <?php while($a = mysqli_fetch_assoc($akun)): ?>
                <option value="<?= $a['id_akun'] ?>" 
                    <?= $id_akun==$a['id_akun']?'selected':'' ?>>
                    <?= $a['kode_akun'] ?> - <?= $a['nama_akun'] ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Tanggal Awal</label>
        <input type="date" name="awal" value="<?= $tanggal_awal ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Tanggal Akhir</label>
        <input type="date" name="akhir" value="<?= $tanggal_akhir ?>" class="form-control">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Tampilkan</button>
    </div>
</form>

<?php if($id_akun != ''): ?>

<div class="card p-3">
    <h6>
        <?= $akun_detail['kode_akun'] ?> - <?= $akun_detail['nama_akun'] ?>
    </h6>

    <div class="table-responsive mt-3">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row = mysqli_fetch_assoc($query)): 

                /* Logika saldo berdasarkan tipe akun */
                if(strtolower($akun_detail['tipe']) == 'aset' || 
                   strtolower($akun_detail['tipe']) == 'beban'){

                    $saldo += $row['debit'];
                    $saldo -= $row['kredit'];

                } else {

                    $saldo += $row['kredit'];
                    $saldo -= $row['debit'];
                }
            ?>

                <tr>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= $row['keterangan'] ?></td>
                    <td class="text-end">
                        <?= $row['debit']>0?format_rp($row['debit']):'' ?>
                    </td>
                    <td class="text-end">
                        <?= $row['kredit']>0?format_rp($row['kredit']):'' ?>
                    </td>
                    <td class="text-end fw-semibold">
                        <?= format_rp($saldo) ?>
                    </td>
                </tr>

            <?php endwhile; ?>

            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>