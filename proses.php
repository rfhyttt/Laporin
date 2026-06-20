<?php
include '../koneksi/koneksi.php';

// Handle proses POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses'])) {
    $proses = $_POST['proses'];
    $nama_akun = mysqli_real_escape_string($koneksi, $_POST['nama_akun']);
    $jenis = mysqli_real_escape_string($koneksi, $_POST['tipe']);

    if ($proses === "add") {
        mysqli_query($koneksi, "INSERT INTO tb_master_akun (nama_akun, tipe) VALUES ('$nama_akun', '$jenis')");
        header('Location: ../akun.php');
        exit;

    } elseif ($proses === "edit_master_akun" && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        mysqli_query($koneksi, "UPDATE tb_master_akun SET nama_akun = '$nama_akun', tipe = '$tipe' WHERE id_akun = '$id'");
        header('Location: ../akun.php');
        exit;
    }

// Handle proses GET
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['proses']) && $_GET['proses'] === "hapus" && isset($_GET['id_akun'])) {
    $id_akun = intval($_GET['id_akun']);

    // Cek apakah akun digunakan di jurnal_entri
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jurnal_entri WHERE id_akun = '$id_akun'");
    $data = mysqli_fetch_assoc($cek);

    if ($data['total'] > 0) {
        echo "<script>alert('Akun tidak bisa dihapus karena masih digunakan dalam transaksi.');history.back();</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM tb_master_akun WHERE id_akun = '$id_akun'");
        header('Location: ../tb_master_akun.php');
        exit;
    }

} else {
    echo "<script>alert('Permintaan tidak valid.');history.back();</script>";
}
