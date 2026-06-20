<?php
session_start();
require 'koneksi/koneksi.php';

if (!empty($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan Password wajib diisi!";
    } else {

        $stmt = mysqli_prepare($koneksi, "SELECT id, username, password FROM tb_user WHERE username = ? LIMIT 1");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            if ($user && password_verify($password, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['login']    = true;

                header("Location: index.php");
                exit;
            } else {
                $error = "Username atau Password salah!";
            }
        } else {
            $error = "Terjadi kesalahan sistem.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — LAPORIN</title>

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

/* Card style sama seperti dashboard */
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

<div class="container" style="max-width:420px;">

    <div class="card p-4">

        <h5 class="section-title text-center mb-2">Login ke LAPORIN</h5>
        <p class="small-muted text-center mb-4">
            Silakan masuk untuk melanjutkan
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary">
                    Login
                </button>
            </div>

            <div class="text-center">
                <a href="register.php" class="small-muted text-decoration-none">
                    Belum punya akun? Registrasi
                </a>
            </div>

        </form>

    </div>

</div>

</body>
</html>