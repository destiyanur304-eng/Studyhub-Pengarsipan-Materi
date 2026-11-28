<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/dashboardController.php';

requireRole('mahasiswa');

$controller = new DashboardController($pdo);

// Mengambil data program studi dan semester mahasiswa
$prodiId = (int) $_SESSION['user']['prodi_id'];
$semester = (int) $_SESSION['user']['semester'];

// Mengambil statistik dashboard mahasiswa
$stats = $controller->getMahasiswaStats($prodiId, $semester);

// Mengambil lima mata kuliah terpopuler sesuai prodi dan semester mahasiswa
$populer = $controller->getMataKuliahTerpopuler(
    $prodiId,
    $semester,
    5
);

$pageTitle = 'Dashboard Mahasiswa';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Dashboard Mahasiswa</h1>
                <p>Akses arsip materi perkuliahan dengan cepat.</p>
            </div>

            <div class="user-info">
                <?= e($_SESSION['user']['nama']); ?>
            </div>
        </div>

        <section class="welcome-card">
            <h2>Halo, <?= e($_SESSION['user']['nama']); ?>!</h2>
            <p>
                Selamat datang di StudyHub. Cari dan buka materi perkuliahan yang kamu butuhkan.
            </p>
        </section>

        <section class="stats-container">

            <div class="stat-card">
                <div class="stat-icon">
                    <?= icon('book'); ?>
                </div>

                <div class="stat-info">
                    <h3>Mata Kuliah</h3>
                    <p><?= e($stats['matakuliah']); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <?= icon('file'); ?>
                </div>

                <div class="stat-info">
                    <h3>Materi Tersedia</h3>
                    <p><?= e($stats['materi']); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <?= icon('calendar'); ?>
                </div>

                <div class="stat-info">
                    <h3>Semester Saya</h3>
                    <p><?= e($semester); ?></p>
                </div>
            </div>

        </section>

        <section class="content-card">
            <h2>Cari Materi</h2>

            <form method="GET" action="arsip.php">
                <input
                    type="text"
                    name="keyword"
                    placeholder="Cari judul materi atau mata kuliah..."
                >

                <button type="submit">Cari</button>
            </form>
        </section>

        <section class="content-card">
            <h2>Mata Kuliah Terpopuler di Prodimu</h2>

            <?php if (count($populer) > 0): ?>

                <div class="file-list">

                    <?php foreach ($populer as $p): ?>

                        <div class="file-row">

                            <div class="file-icon">
                                <?= icon('book'); ?>
                            </div>

                            <div class="file-name">
                                <strong><?= e($p['nama_mk']); ?></strong>

                                <small>
                                    Semester <?= (int) $semester; ?>
                                </small>
                            </div>

                            <span class="file-badge">
                                <?= (int) $p['jumlah_akses']; ?>x diakses
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p class="info-text">
                    Belum ada mata kuliah yang tersedia pada semester ini.
                </p>

            <?php endif; ?>
        </section>

        <section class="info-grid">

            <div class="content-card">
                <h2>Tentang StudyHub</h2>

                <p class="info-text">
                    StudyHub adalah website pengarsipan materi perkuliahan.
                    Materi yang tersimpan di Google Drive dikumpulkan dan
                    disusun berdasarkan tahun arsip, semester, dan mata kuliah,
                    sehingga lebih mudah dicari dan dibuka kapan saja.
                </p>

                <p class="info-text">
                    Setiap materi dapat dilihat langsung di halaman StudyHub dan
                    diunduh dalam bentuk PDF atau PPT. Materi dikelola oleh admin
                    dan dapat diakses sesuai program studimu.
                </p>
            </div>

            <div class="content-card">
                <h2>Cara Menggunakan</h2>

                <ol class="steps">
                    <li>Buka menu <strong>Arsip Materi</strong> di sidebar.</li>

                    <li>
                        Pilih <strong>tahun arsip</strong>, lalu
                        <strong>semester</strong>, lalu folder
                        <strong>mata kuliah</strong>.
                    </li>

                    <li>
                        Di dalam folder, cari nama materi atau saring
                        berdasarkan kategori (Teori, Praktikum, dan lainnya).
                    </li>

                    <li>
                        Klik nama berkas untuk melihatnya, lalu tekan
                        <strong>Unduh</strong> jika ingin menyimpannya.
                    </li>

                    <li>
                        Sudah tahu nama materinya? Pakai tab
                        <strong>Cari dan filter</strong> untuk mencari
                        lintas tahun dan semester.
                    </li>
                </ol>
            </div>

        </section>

        <section class="content-card">
            <h2>Struktur Arsip</h2>

            <div class="chip-flow">
                <span class="chip">Tahun Arsip</span>
                <span class="chip-arrow">&rarr;</span>
                <span class="chip">Semester</span>
                <span class="chip-arrow">&rarr;</span>
                <span class="chip">Mata Kuliah</span>
                <span class="chip-arrow">&rarr;</span>
                <span class="chip">Berkas (Teori dan Praktikum)</span>
            </div>

            <p class="info-text">
                Tahun arsip tersedia dari 2023 sampai 2026. D3 Manajemen Informatika
                memiliki semester 1 sampai 6, sedangkan S1 Ilmu Komputer dan
                S1 Sistem Informasi memiliki semester 1 sampai 8. Kamu dapat membuka
                seluruh semester pada program studimu.
            </p>
        </section>

    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>