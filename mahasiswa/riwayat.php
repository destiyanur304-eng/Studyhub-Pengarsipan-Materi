<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/arsipController.php';

requireRole('mahasiswa');

$ctrl = new ArsipController($pdo);

// Mengambil profil mahasiswa yang sedang login
$profil = $ctrl->profil($_SESSION['user']['id']);

if (!$profil) {
    die('Data program studi akunmu belum lengkap. Hubungi admin.');
}

// Mengambil riwayat akses mahasiswa
$riwayat = $ctrl->riwayatAkses(
    (int) $profil['id'],
    (int) $profil['prodi_id']
);

$pageTitle = 'Riwayat Akses';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Riwayat Akses</h1>
                <p>Daftar materi yang pernah kamu buka</p>
            </div>
        </div>

        <div class="arsip-box">

            <div class="riwayat-info">
                <strong><?= count($riwayat); ?> materi pernah dibuka</strong>
                <p>
                    Riwayat ini menampilkan materi yang pernah kamu akses,
                    berdasarkan waktu akses terakhir.
                </p>
            </div>

        </div>

        <?php if (count($riwayat) > 0): ?>

            <div class="file-list">

                <?php foreach ($riwayat as $r): ?>

                    <a class="file-row"
                       href="buka.php?id=<?= (int) $r['materi_id']; ?>">

                        <div class="file-icon">
                            <?= icon('file'); ?>
                        </div>

                        <div class="file-name">

                            <strong><?= e($r['judul']); ?></strong>

                            <small>
                                <?= e($r['nama_mk']); ?>
                                &middot;
                                Semester <?= (int) $r['semester']; ?>
                                &middot;
                                Tahun <?= e($r['tahun_arsip']); ?>
                            </small>

                            <small>
                                Terakhir dibuka:
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime($r['waktu_terakhir'])
                                ); ?>
                            </small>

                        </div>

                        <span class="file-badge">
                            <?= e($r['nama_kategori']); ?>
                        </span>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <div><?= icon('folder'); ?></div>
                <strong>Belum Ada Riwayat Akses</strong>
                <p>
                    Materi yang kamu buka nantinya akan muncul
                    di halaman ini.
                </p>

                <a href="arsip.php" class="btn-secondary">
                    Jelajahi Arsip Materi
                </a>
            </div>

        <?php endif; ?>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>