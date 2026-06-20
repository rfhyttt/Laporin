<?php
session_start();
require 'koneksi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi kosong
if ($username === '' || $password === '') {
    $_SESSION['error'] = "Username dan Password wajib diisi!";
    header("Location: login.php");
    exit;
}

// Gunakan tabel tb_user (samakan dengan sistem Anda)
$stmt = mysqli_prepare($koneksi, 
    "SELECT id_user, username, password FROM tb_user WHERE username = ? LIMIT 1"
);

if (!$stmt) {
    $_SESSION['error'] = "Terjadi kesalahan sistem.";
    header("Location: login.php");
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {

    // Anti session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']  = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login']    = true;

    header("Location: index.php");
    exit;

} else {
    $_SESSION['error'] = "Username atau Password salah!";
    header("Location: login.php");
    exit;
}
?>