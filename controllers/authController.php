<?php

function processLogin($pdo)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'] ?? '',
            $_POST['csrf_token']
        )
    ) {
        return "Permintaan tidak valid. Silakan coba lagi.";
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        return "Username dan password wajib diisi.";
    }

    // 1. Periksa akun Admin dari tabel users
    $queryAdmin = $pdo->prepare(
        "SELECT id, nama, username, password
         FROM users
         WHERE username = ?
         LIMIT 1"
    );

    $queryAdmin->execute([$username]);
    $admin = $queryAdmin->fetch();

    if ($admin && password_verify($password, $admin['password'])) {

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $admin['id'],
            'nama' => $admin['nama'],
            'username' => $admin['username'],
            'role' => 'admin',
            'nim' => null,
            'prodi_id' => null,
            'semester' => null
        ];

        unset($_SESSION['csrf_token']);

        redirect('/index.php');
    }

    // 2. Jika bukan Admin, periksa akun Mahasiswa
    $queryMahasiswa = $pdo->prepare(
        "SELECT id, nama, nim, username, password,
                prodi_id, semester
         FROM mahasiswa
         WHERE username = ?
         LIMIT 1"
    );

    $queryMahasiswa->execute([$username]);
    $mahasiswa = $queryMahasiswa->fetch();

    if (
        $mahasiswa &&
        password_verify($password, $mahasiswa['password'])
    ) {

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => (int) $mahasiswa['id'],
            'nama' => $mahasiswa['nama'],
            'username' => $mahasiswa['username'],
            'role' => 'mahasiswa',
            'nim' => $mahasiswa['nim'],
            'prodi_id' => (int) $mahasiswa['prodi_id'],
            'semester' => (int) $mahasiswa['semester']
        ];

        unset($_SESSION['csrf_token']);

        redirect('/index.php');
    }

    return "Username atau password salah.";
}