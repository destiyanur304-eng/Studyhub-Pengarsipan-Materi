<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/controllers/mahasiswaController.php';

// Kalau sudah login, tidak perlu daftar
if (isset($_SESSION['user'])) {
    redirect('/index.php');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$old = [
    'nama'     => '',
    'nim'      => '',
    'username' => '',
    'prodi_id' => '',
    'semester' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Permintaan tidak valid.');
    }

    // Ambil input. Role TIDAK diambil dari form (dipaksa mahasiswa di model).
    $data = [
        'nama'     => trim($_POST['nama'] ?? ''),
        'nim'      => trim($_POST['nim'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'prodi_id' => $_POST['prodi_id'] ?? '',
        'semester' => $_POST['semester'] ?? '',
    ];

    $konfirmasi = $_POST['konfirmasi'] ?? '';

    // Simpan isian lama (kecuali password) agar tidak mengetik ulang
    foreach ($old as $key => $value) {
        $old[$key] = $data[$key];
    }

    // Validasi tambahan khusus registrasi
    if (!preg_match('/^[0-9]{5,20}$/', $data['nim'])) {
        $error = 'NIM harus berupa angka (5-20 digit).';

    } elseif (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $data['username'])) {
        $error = 'Username 4-30 karakter: huruf, angka, titik, atau garis bawah.';

    } elseif ($data['password'] !== $konfirmasi) {
        $error = 'Konfirmasi password tidak sama.';

    } else {
        // Validasi lain (wajib isi, panjang password, prodi, semester,
        // dan NIM/username unik) ditangani oleh controller
        $controller = new MahasiswaController($pdo);
        $hasil = $controller->store($data);

        if ($hasil['success']) {
            redirect('/login.php?registered=1');
        }

        $error = $hasil['message'];
    }
}

$prodi = $pdo->query(
    "SELECT * FROM program_studi ORDER BY nama_prodi"
)->fetchAll();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar - StudyHub</title>

    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css">
</head>
<body>

<div class="login-container">

    <div class="login-card">

        <h1>StudyHub</h1>

        <p class="subtitle">
            Sistem Pengarsipan Materi Perkuliahan
        </p>

        <h2>Daftar Akun Mahasiswa</h2>

        <p class="description">
            Isi data berikut untuk membuat akun StudyHub.
        </p>

        <?php if ($error): ?>
            <div class="error">
                <?= e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="formRegistrasi">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($_SESSION['csrf_token']); ?>"
            >

            <label for="nama">Nama Lengkap</label>
            <input
                type="text"
                id="nama"
                name="nama"
                placeholder="Masukkan nama lengkap"
                value="<?= e($old['nama']); ?>"
                required
            >

            <label for="nim">NIM</label>
            <input
                type="text"
                id="nim"
                name="nim"
                placeholder="Masukkan NIM"
                pattern="[0-9]{5,20}"
                title="NIM hanya boleh angka (5-20 digit)"
                value="<?= e($old['nim']); ?>"
                required
            >

            <label for="prodi_id">Program Studi</label>
            <select id="prodi_id" name="prodi_id" required>
                <option value="">Pilih Program Studi</option>

                <?php foreach ($prodi as $p): ?>
                    <option value="<?= e($p['id']); ?>"
                        <?= (string) $old['prodi_id'] === (string) $p['id'] ? 'selected' : ''; ?>>
                        <?= e($p['nama_prodi']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="semester">Semester</label>
            <select id="semester" name="semester" required>
                <option value="">Pilih Semester</option>

                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <option value="<?= $i; ?>"
                        <?= (string) $old['semester'] === (string) $i ? 'selected' : ''; ?>>
                        Semester <?= $i; ?>
                    </option>
                <?php endfor; ?>
            </select>

            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Contoh: budi.santoso"
                pattern="[A-Za-z0-9_.]{4,30}"
                title="4-30 karakter: huruf, angka, titik, atau garis bawah"
                value="<?= e($old['username']); ?>"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimal 8 karakter"
                minlength="8"
                required
            >

            <label for="konfirmasi">Konfirmasi Password</label>
            <input
                type="password"
                id="konfirmasi"
                name="konfirmasi"
                placeholder="Ulangi password"
                minlength="8"
                required
            >

            <button type="submit">
                Daftar
            </button>

        </form>

        <p class="auth-link">
            Sudah punya akun?
            <a href="<?= BASE_URL; ?>/login.php">Login di sini</a>
        </p>

    </div>

</div>

<script>
    // Validasi sisi klien: konfirmasi password harus sama
    document.getElementById('formRegistrasi').addEventListener('submit', function (event) {
        const pw = document.getElementById('password').value;
        const kf = document.getElementById('konfirmasi').value;

        if (pw !== kf) {
            event.preventDefault();
            alert('Konfirmasi password tidak sama.');
        }
    });
</script>

</body>
</html>