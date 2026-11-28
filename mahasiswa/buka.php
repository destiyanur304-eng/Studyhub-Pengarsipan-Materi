<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/arsipController.php';
require_once __DIR__ . '/../controllers/ulasanController.php';

requireRole('mahasiswa');

// Memastikan ID materi berupa angka yang valid
$id = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    die('Permintaan tidak valid.');
}

// Membuat controller arsip
$ctrl = new ArsipController($pdo);

// Mengambil profil mahasiswa dan detail materi
$mahasiswaId = (int) $_SESSION['user']['id'];

$profil = $ctrl->profil($mahasiswaId);
$materi = $ctrl->materiUntukAkses($id);

// Memastikan data tersedia
if (!$profil || !$materi) {
    http_response_code(404);
    die('Materi tidak ditemukan.');
}

// Memastikan materi berasal dari program studi mahasiswa
if (!$ctrl->bolehAkses($profil, $materi)) {
    http_response_code(403);
    die('Kamu tidak memiliki akses ke materi ini.');
}

// Membuat controller ulasan
$ulasanCtrl = new UlasanController($pdo);

// Proses kirim, ubah, dan hapus ulasan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Permintaan tidak valid.');
    }

    $hasil = null;

    if (isset($_POST['simpan_ulasan'])) {

        $hasil = $ulasanCtrl->simpan(
            $materi['id'],
            $mahasiswaId,
            $_POST
        );

    } elseif (isset($_POST['hapus_ulasan'])) {

        $hasil = $ulasanCtrl->hapus($materi['id'], $mahasiswaId);
    }

    if ($hasil !== null) {
        setFlash(
            $hasil['message'],
            $hasil['success'] ? 'success' : 'error'
        );

        // Kembali dari proses ulasan tidak dihitung sebagai akses baru
        $_SESSION['lewati_akses'] = (int) $materi['id'];
    }

    redirect('/mahasiswa/buka.php?id=' . (int) $materi['id'] . '#ulasan');
}

// Memeriksa tautan Google Drive atau Google Docs
$info = $ctrl->infoBerkas($materi['link_drive']);

$urlPratinjau = $ctrl->urlPratinjau($info);
$urlUnduh = $ctrl->urlUnduh($info);

// Mencatat akses mahasiswa.
// Trigger database otomatis menambah jumlah_dilihat.
if (($_SESSION['lewati_akses'] ?? 0) === (int) $materi['id']) {
    unset($_SESSION['lewati_akses']);
} else {
    $ctrl->catatAkses($mahasiswaId, $materi['id']);
}

// Data rating dan ulasan
$ringkasan = $ulasanCtrl->ringkasan($materi['id']);
$distribusi = $ulasanCtrl->distribusi($materi['id']);
$ulasanSaya = $ulasanCtrl->ulasanSaya($materi['id'], $mahasiswaId);
$ulasanLain = $ulasanCtrl->daftarLain($materi['id'], $mahasiswaId);
$flash = getFlash();

$pageTitle = $materi['judul'];

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="viewer-head">

            <div class="viewer-title">

                <h1><?= e($materi['judul']); ?></h1>

                <div class="viewer-meta">

                    <span class="file-badge">
                        <?= e($materi['nama_mk']); ?>
                    </span>

                    <span class="file-badge">
                        Semester <?= e($materi['semester']); ?>
                    </span>

                    <span class="file-badge">
                        <?= e($materi['kategori']); ?>
                    </span>

                    <span class="file-badge">
                        <?= e($materi['jenis_berkas']); ?>
                    </span>

                    <span class="file-badge">
                        Tahun <?= e($materi['tahun_arsip']); ?>
                    </span>

                </div>

            </div>

            <div class="viewer-actions">

                <a
                    href="arsip.php"
                    class="btn-secondary"
                    onclick="if (history.length > 1) { history.back(); return false; }"
                >
                    &larr; Kembali
                </a>

                <?php if ($urlUnduh): ?>

                    <a
                        href="<?= e($urlUnduh); ?>"
                        class="btn-primary"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Unduh
                    </a>

                <?php endif; ?>

                <?php if ($urlPratinjau): ?>

                    <button type="button" id="btnFull">
                        Layar Penuh
                    </button>

                <?php endif; ?>

            </div>

        </div>

        <?php if (!empty($materi['deskripsi'])): ?>

            <div class="content-card">
                <h3>Deskripsi Materi</h3>

                <p class="info-text">
                    <?= e($materi['deskripsi']); ?>
                </p>
            </div>

        <?php endif; ?>

        <?php if ($urlPratinjau): ?>

            <div class="viewer-frame" id="viewerFrame">

                <iframe
                    src="<?= e($urlPratinjau); ?>"
                    title="<?= e($materi['judul']); ?>"
                    referrerpolicy="no-referrer"
                    allowfullscreen>
                </iframe>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <h3>Pratinjau Tidak Tersedia</h3>

                <p>
                    Tautan materi ini belum dapat ditampilkan.
                    Pastikan admin menggunakan tautan file Google Drive
                    atau Google Docs yang valid, bukan tautan folder.
                </p>

            </div>

        <?php endif; ?>

        <!-- Rating dan ulasan materi -->
        <section id="ulasan" class="ulasan-section">

            <div class="page-heading">
                <h1>Rating &amp; Ulasan</h1>
                <p>Bantu mahasiswa lain menilai materi ini.</p>
            </div>

            <?php if ($flash): ?>

                <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success'; ?>">
                    <?= e($flash['message']); ?>
                </div>

            <?php endif; ?>

            <div class="info-grid">

                <div class="content-card">

                    <h2>Rata-rata Rating</h2>

                    <?php if ($ringkasan['jumlah'] > 0): ?>

                        <div class="rating-summary">

                            <div class="rating-score">
                                <strong><?= e(formatRating($ringkasan['rata'])); ?></strong>
                                <?= bintangHtml($ringkasan['rata']); ?>
                                <small><?= (int) $ringkasan['jumlah']; ?> ulasan</small>
                            </div>

                            <div class="rating-bars">

                                <?php for ($b = 5; $b >= 1; $b--): ?>

                                    <?php
                                    $persen = (int) round(
                                        $distribusi[$b] / $ringkasan['jumlah'] * 100
                                    );
                                    ?>

                                    <div class="rating-bar">
                                        <span><?= $b; ?> &#9733;</span>
                                        <div class="bar">
                                            <i style="width: <?= $persen; ?>%"></i>
                                        </div>
                                        <span><?= (int) $distribusi[$b]; ?></span>
                                    </div>

                                <?php endfor; ?>

                            </div>

                        </div>

                    <?php else: ?>

                        <p class="info-text">
                            Belum ada rating untuk materi ini.
                            Jadilah yang pertama memberi ulasan.
                        </p>

                    <?php endif; ?>

                </div>

                <div class="content-card">

                    <h2><?= $ulasanSaya ? 'Ulasanmu' : 'Beri Ulasan'; ?></h2>

                    <form method="POST" action="#ulasan">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION['csrf_token']); ?>"
                        >

                        <label>Rating</label>

                        <div class="star-input">

                            <?php for ($n = 5; $n >= 1; $n--): ?>

                                <input
                                    type="radio"
                                    name="rating"
                                    id="rating<?= $n; ?>"
                                    value="<?= $n; ?>"
                                    <?= (int) ($ulasanSaya['rating'] ?? 0) === $n ? 'checked' : ''; ?>
                                    required
                                >

                                <label
                                    for="rating<?= $n; ?>"
                                    title="<?= $n; ?> bintang"
                                >&#9733;</label>

                            <?php endfor; ?>

                        </div>

                        <label>Ulasan</label>

                        <textarea
                            name="komentar"
                            maxlength="1000"
                            placeholder="Tulis pendapatmu tentang materi ini (opsional)"
                        ><?= e($ulasanSaya['komentar'] ?? ''); ?></textarea>

                        <button type="submit" name="simpan_ulasan" value="1">
                            <?= $ulasanSaya ? 'Perbarui Ulasan' : 'Kirim Ulasan'; ?>
                        </button>

                    </form>

                    <?php if ($ulasanSaya): ?>

                        <div class="action-buttons ulasan-hapus">

                            <form
                                method="POST"
                                action="#ulasan"
                                onsubmit="return confirm('Hapus ulasanmu untuk materi ini?');"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= e($_SESSION['csrf_token']); ?>"
                                >

                                <button type="submit" name="hapus_ulasan" value="1" class="btn-danger">
                                    Hapus Ulasanku
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <div class="content-card">

                <h2>Ulasan Mahasiswa Lain (<?= count($ulasanLain); ?>)</h2>

                <?php if (count($ulasanLain) > 0): ?>

                    <div class="review-list">

                        <?php foreach ($ulasanLain as $u): ?>

                            <article class="review-item">

                                <div class="review-avatar">
                                    <?= e(mb_strtoupper(mb_substr($u['nama'], 0, 1, 'UTF-8'), 'UTF-8')); ?>
                                </div>

                                <div class="review-body">

                                    <div class="review-head">
                                        <strong><?= e($u['nama']); ?></strong>
                                        <?= bintangHtml($u['rating']); ?>
                                        <small><?= e(formatWaktu($u['diperbarui_pada'])); ?></small>
                                    </div>

                                    <?php if (!empty($u['komentar'])): ?>
                                        <p><?= nl2br(e($u['komentar'])); ?></p>
                                    <?php endif; ?>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="info-text">
                        Belum ada ulasan dari mahasiswa lain.
                    </p>

                <?php endif; ?>

            </div>

        </section>

    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tombol = document.getElementById('btnFull');
    const bingkai = document.getElementById('viewerFrame');

    if (tombol && bingkai) {

        tombol.addEventListener('click', function () {

            if (bingkai.requestFullscreen) {
                bingkai.requestFullscreen();
            }

        });

    }

});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>