<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/dashboardController.php';

requireRole('admin');

$controller = new DashboardController($pdo);
$stats = $controller->getAdminStats();

$pageTitle = 'Dashboard Admin';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Dashboard Admin</h1>
                <p>Selamat datang di halaman pengelolaan StudyHub.</p>
            </div>

            <div class="user-info">
                <?= e($_SESSION['user']['nama']); ?>
            </div>
        </div>

        <section class="welcome-card">
            <h2>Selamat Datang, Admin!</h2>

            <p>
                Kelola data mahasiswa, mata kuliah, dan materi
                perkuliahan melalui sistem StudyHub.
            </p>
        </section>

        <section class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <?= icon('users'); ?>
                </div>
                <div class="stat-info">
                    <h3>Data Mahasiswa</h3>
                    <p><?= e($stats['mahasiswa']); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <?= icon('book'); ?>
                </div>
                <div class="stat-info">
                    <h3>Mata Kuliah</h3>
                    <p><?= e($stats['matakuliah']); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <?= icon('file'); ?>
                </div>
                <div class="stat-info">
                    <h3>Materi Perkuliahan</h3>
                    <p><?= e($stats['materi']); ?></p>
                </div>
            </div>
        </section>

    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>