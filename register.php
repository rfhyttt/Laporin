<?php
session_start();
include 'koneksi/koneksi.php';

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_user = trim($_POST['nama_user']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (empty($nama_user) || empty($username) || empty($password) || empty($konfirmasi_password)) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($password !== $konfirmasi_password) {
        $error = "Password dan konfirmasi tidak cocok!";
    } else {

        $cek = mysqli_prepare($koneksi, "SELECT id FROM tb_user WHERE username = ?");
        mysqli_stmt_bind_param($cek, 's', $username);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Username sudah digunakan!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($koneksi, "INSERT INTO tb_user (nama_user, username, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $nama_user, $username, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location.href='login.php';</script>";
                exit;
            } else {
                $error = "Registrasi gagal!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi — LAPORIN</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #f6f8fb;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
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

.small-muted {
    font-size: 13px;
    color: #6c757d;
}

.form-label {
    font-weight: 500;
    font-size: 14px;
}

.form-control {
    border-radius: 10px;
    font-size: 14px;
}

.btn-primary {
    border-radius: 10px;
    font-size: 14px;
}

.btn-outline-primary {
    border-radius: 10px;
    font-size: 14px;
}
</style>
</head>

<body>

<div class="container" style="max-width:460px;">

    <div class="card p-4">

        <h5 class="section-title text-center mb-2">Buat Akun Baru</h5>
        <p class="small-muted text-center mb-4">
            Daftar untuk mulai menggunakan LAPORIN by UNRI
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama_user" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="konfirmasi_password" class="form-control" required>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">
                    Daftar
                </button>
            </div>

            <div class="text-center">
                <a href="login.php" class="small-muted text-decoration-none">
                    Sudah punya akun? Login
                </a>
            </div>

        </form>

    </div>

</div>

</body>
</html>