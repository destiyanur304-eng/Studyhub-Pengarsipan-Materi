<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('mahasiswa');

$idMahasiswa = (int) $_SESSION['user']['id'];

$query = $pdo->prepare(
    "SELECT
        mahasiswa.id,
        mahasiswa.nama,
        mahasiswa.nim,
        mahasiswa.username,
        mahasiswa.semester,
        mahasiswa.prodi_id,
        program_studi.nama_prodi
     FROM mahasiswa
     LEFT JOIN program_studi
        ON mahasiswa.prodi_id = program_studi.id
     WHERE mahasiswa.id = ?
     LIMIT 1"
);

$query->execute([$idMahasiswa]);

$data = $query->fetch();

if (!$data) {
    die('Data mahasiswa tidak ditemukan. Silakan login ulang.');
}

$namaProdi = $data['nama_prodi'] ?? 'Program Studi belum tersedia';

$inisial = mb_strtoupper(
    mb_substr($data['nama'], 0, 1, 'UTF-8'),
    'UTF-8'
);

$pageTitle = 'Profil Saya';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Profil Saya</h1>
                <p>Informasi akun mahasiswa.</p>
            </div>
        </div>

        <section class="profile-card">

            <div class="profile-header">

                <div class="profile-avatar">
                    <?= e($inisial); ?>
                </div>

                <div class="profile-identity">

                    <h2><?= e($data['nama']); ?></h2>

                    <p>NIM <?= e($data['nim']); ?></p>

                    <div class="profile-badges">

                        <span class="badge">
                            <?= e($namaProdi); ?>
                        </span>

                        <span class="badge">
                            Semester <?= e($data['semester']); ?>
                        </span>

                    </div>

                </div>

            </div>

            <div class="profile-body">

                <h3>Informasi Akun</h3>

                <div class="profile-grid">

                    <div class="profile-item">
                        <span class="profile-label">
                            Nama Lengkap
                        </span>

                        <span class="profile-value">
                            <?= e($data['nama']); ?>
                        </span>
                    </div>

                    <div class="profile-item">
                        <span class="profile-label">
                            NIM
                        </span>

                        <span class="profile-value">
                            <?= e($data['nim']); ?>
                        </span>
                    </div>

                    <div class="profile-item">
                        <span class="profile-label">
                            Program Studi
                        </span>

                        <span class="profile-value">
                            <?= e($namaProdi); ?>
                        </span>
                    </div>

                    <div class="profile-item">
                        <span class="profile-label">
                            Semester
                        </span>

                        <span class="profile-value">
                            <?= e($data['semester']); ?>
                        </span>
                    </div>

                    <div class="profile-item">
                        <span class="profile-label">
                            Username
                        </span>

                        <span class="profile-value">
                            <?= e($data['username']); ?>
                        </span>
                    </div>

                    <div class="profile-item">
                        <span class="profile-label">
                            Peran
                        </span>

                        <span class="profile-value">
                            Mahasiswa
                        </span>
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>