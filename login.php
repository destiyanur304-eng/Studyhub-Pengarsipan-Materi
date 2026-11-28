<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/controllers/authController.php';

if (isset($_SESSION['user'])) {
    redirect('/index.php');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = processLogin($pdo);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - StudyHub</title>

    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>
<body>

<div class="login-container">

    <div class="login-card">

        <h1>StudyHub</h1>

        <p class="subtitle">
            Sistem Pengarsipan Materi Perkuliahan
        </p>

        <h2>Selamat Datang</h2>

        <p class="description">
            Silakan login untuk mengakses StudyHub.
        </p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="success">
                Pendaftaran berhasil. Silakan login.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">
                <?= e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']); ?>"
            >

            <label for="username">Username</label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Masukkan username"
                required
            >

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                required>

            <button type="submit">Login</button>

        </form>

        <p class="auth-link">
            Belum punya akun?
            <a href="<?= BASE_URL; ?>/register.php">Daftar sebagai mahasiswa</a>
        </p>

        </form>

    </div>

</div>

</body>
</html>
