<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/arsipController.php';
require_once __DIR__ . '/../controllers/forumController.php';

requireRole('mahasiswa');

// Mengambil parameter URL sebagai teks (menolak array)
function getParam($key)
{
    return (isset($_GET[$key]) && is_string($_GET[$key]))
        ? trim($_GET[$key])
        : '';
}

$arsip = new ArsipController($pdo);
$forum = new ForumController($pdo);

$mahasiswaId = (int) $_SESSION['user']['id'];
$profil = $arsip->profil($mahasiswaId);

if (!$profil) {
    die('Data program studi akunmu belum lengkap. Hubungi admin.');
}

$prodiId = (int) $profil['prodi_id'];

$kategoriList = $forum->daftarKategori();
$statusList = $forum->daftarStatus();
$mataKuliahList = $forum->daftarMataKuliah($prodiId);

$pesan = '';
$berhasil = false;
$lama = ['kategori' => '', 'judul' => '', 'isi' => '', 'mata_kuliah_id' => ''];

// ID postingan yang sedang dibuka (0 = tampilkan daftar)
$postinganId = filter_var(getParam('id'), FILTER_VALIDATE_INT);
$postinganId = ($postinganId !== false && $postinganId > 0) ? $postinganId : 0;

/* =========================================
   PROSES FORM (POST)
   ========================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Permintaan tidak valid.');
    }

    $targetId = filter_var($_POST['postingan_id'] ?? null, FILTER_VALIDATE_INT);
    $targetId = ($targetId !== false && $targetId > 0) ? $targetId : 0;

    $hasil = null;
    $kembaliKe = '/mahasiswa/diskusi.php';

    if (isset($_POST['buat_postingan'])) {

        $hasil = $forum->buat($mahasiswaId, $prodiId, $_POST);

        if ($hasil['success']) {
            setFlash($hasil['message'], 'success');
            redirect('/mahasiswa/diskusi.php?id=' . (int) $hasil['id']);
        }

        // Gagal: tampilkan ulang form dengan isian sebelumnya
        foreach ($lama as $kunci => $nilai) {
            $lama[$kunci] = is_string($_POST[$kunci] ?? null)
                ? $_POST[$kunci]
                : '';
        }

    } elseif (isset($_POST['balas'])) {

        $hasil = $forum->balas(
            $targetId,
            $mahasiswaId,
            $prodiId,
            $_POST['isi'] ?? ''
        );
        $kembaliKe .= '?id=' . $targetId . '#balasan';

    } elseif (isset($_POST['ubah_status'])) {

        $hasil = $forum->ubahStatus(
            $targetId,
            $mahasiswaId,
            $prodiId,
            is_string($_POST['status'] ?? null) ? $_POST['status'] : ''
        );
        $kembaliKe .= '?id=' . $targetId;

    } elseif (isset($_POST['hapus_postingan'])) {

        $hasil = $forum->hapusPostingan($targetId, $mahasiswaId, $prodiId);

        if (!$hasil['success']) {
            $kembaliKe .= '?id=' . $targetId;
        }

    } elseif (isset($_POST['hapus_balasan'])) {

        $hasil = $forum->hapusBalasan(
            $_POST['balasan_id'] ?? 0,
            $targetId,
            $mahasiswaId,
            $prodiId
        );
        $kembaliKe .= '?id=' . $targetId . '#balasan';
    }

    if ($hasil !== null && !isset($_POST['buat_postingan'])) {
        setFlash(
            $hasil['message'],
            $hasil['success'] ? 'success' : 'error'
        );
        redirect($kembaliKe);
    }

    if ($hasil !== null) {
        // Hanya sampai di sini bila pembuatan postingan gagal
        $pesan = $hasil['message'];
        $berhasil = false;
    }
}

/* =========================================
   AMBIL DATA UNTUK TAMPILAN
   ========================================= */

$postingan = null;
$balasan = [];
$daftar = [];
$q = '';
$fKategori = '';
$fStatus = '';
$fMk = '';

if ($postinganId > 0) {

    $postingan = $forum->show($postinganId, $prodiId);

    if (!$postingan) {
        http_response_code(404);
        die('Postingan tidak ditemukan.');
    }

    $balasan = $forum->balasan($postingan['id']);

} else {

    $q = mb_substr(getParam('q'), 0, 100);

    if (array_key_exists(getParam('kategori'), $kategoriList)) {
        $fKategori = getParam('kategori');
    }

    if (array_key_exists(getParam('status'), $statusList)) {
        $fStatus = getParam('status');
    }

    $mkReq = filter_var(getParam('mk'), FILTER_VALIDATE_INT);
    if ($mkReq !== false && $mkReq > 0) {
        $fMk = (string) $mkReq;
    }

    $daftar = $forum->index($prodiId, [
        'keyword'        => $q,
        'kategori'       => $fKategori,
        'status'         => $fStatus,
        'mata_kuliah_id' => $fMk
    ]);
}

$flash = getFlash();

$pageTitle = $postingan ? $postingan['judul'] : 'Forum Diskusi';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <?php if ($flash): ?>

            <div class="alert <?= $flash['type'] === 'error' ? 'alert-error' : 'alert-success'; ?>">
                <?= e($flash['message']); ?>
            </div>

        <?php endif; ?>

        <?php if ($postingan): ?>

            <?php
            $adalahPembuat = (int) $postingan['mahasiswa_id'] === $mahasiswaId;
            $selesai = $postingan['status'] === 'selesai';
            ?>

            <!-- ===== DETAIL POSTINGAN (THREAD) ===== -->

            <div class="topbar">
                <div>
                    <h1>Forum Diskusi</h1>
                    <p><?= e($profil['nama_prodi']); ?></p>
                </div>

                <a href="diskusi.php" class="btn-secondary">&larr; Kembali ke Forum</a>
            </div>

            <article class="content-card thread-post">

                <div class="viewer-meta">

                    <span class="file-badge">
                        <?= e($kategoriList[$postingan['kategori']] ?? $postingan['kategori']); ?>
                    </span>

                    <span class="file-badge status-<?= e($postingan['status']); ?>">
                        <?= e($statusList[$postingan['status']]); ?>
                    </span>

                    <?php if (!empty($postingan['nama_mk'])): ?>
                        <span class="file-badge">
                            <?= e($postingan['nama_mk']); ?>
                        </span>
                    <?php endif; ?>

                </div>

                <h2 class="thread-title"><?= e($postingan['judul']); ?></h2>

                <p class="thread-meta">
                    Oleh <b><?= e($postingan['nama_penulis']); ?></b>
                    &middot; <?= e(formatWaktu($postingan['dibuat_pada'])); ?>
                </p>

                <p class="thread-isi"><?= nl2br(e($postingan['isi'])); ?></p>

                <?php if ($adalahPembuat): ?>

                    <div class="action-buttons">

                        <form method="POST">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="postingan_id"
                                   value="<?= (int) $postingan['id']; ?>">
                            <input type="hidden" name="status"
                                   value="<?= $selesai ? 'terbuka' : 'selesai'; ?>">

                            <?php if ($selesai): ?>
                                <button type="submit" name="ubah_status" value="1"
                                        class="btn-secondary">
                                    Buka Kembali Diskusi
                                </button>
                            <?php else: ?>
                                <button type="submit" name="ubah_status" value="1"
                                        class="btn-primary">
                                    &#10003; Tandai Terjawab / Selesai
                                </button>
                            <?php endif; ?>
                        </form>

                        <form method="POST"
                              onsubmit="return confirm('Hapus postingan ini beserta semua tanggapannya?');">
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="postingan_id"
                                   value="<?= (int) $postingan['id']; ?>">

                            <button type="submit" name="hapus_postingan" value="1"
                                    class="btn-danger">
                                Hapus Postingan
                            </button>
                        </form>

                    </div>

                <?php endif; ?>

            </article>

            <section id="balasan" class="content-card">

                <h2>Tanggapan (<?= count($balasan); ?>)</h2>

                <?php if (count($balasan) > 0): ?>

                    <div class="review-list">

                        <?php foreach ($balasan as $b): ?>

                            <article class="review-item">

                                <div class="review-avatar">
                                    <?= e(mb_strtoupper(mb_substr($b['nama'], 0, 1, 'UTF-8'), 'UTF-8')); ?>
                                </div>

                                <div class="review-body">

                                    <div class="review-head">

                                        <strong><?= e($b['nama']); ?></strong>

                                        <?php if ((int) $b['mahasiswa_id'] === (int) $postingan['mahasiswa_id']): ?>
                                            <span class="file-badge">Penanya</span>
                                        <?php endif; ?>

                                        <small>
                                            Semester <?= (int) $b['semester']; ?>
                                            &middot;
                                            <?= e(formatWaktu($b['dibuat_pada'])); ?>
                                        </small>

                                    </div>

                                    <p><?= nl2br(e($b['isi'])); ?></p>

                                    <?php if ((int) $b['mahasiswa_id'] === $mahasiswaId): ?>

                                        <form method="POST" class="review-aksi"
                                              onsubmit="return confirm('Hapus tanggapan ini?');">
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= e($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="postingan_id"
                                                   value="<?= (int) $postingan['id']; ?>">
                                            <input type="hidden" name="balasan_id"
                                                   value="<?= (int) $b['id']; ?>">

                                            <button type="submit" name="hapus_balasan" value="1"
                                                    class="btn-danger">
                                                Hapus
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="info-text">
                        Belum ada tanggapan. Jadilah yang pertama membantu.
                    </p>

                <?php endif; ?>

            </section>

            <section class="content-card">

                <h2>Tulis Tanggapan</h2>

                <?php if ($selesai): ?>

                    <div class="alert">
                        Diskusi ini sudah ditandai terjawab / selesai.
                        <?php if ($adalahPembuat): ?>
                            Buka kembali diskusi untuk menerima tanggapan baru.
                        <?php else: ?>
                            Tanggapan baru tidak dapat ditambahkan.
                        <?php endif; ?>
                    </div>

                <?php else: ?>

                    <form method="POST">

                        <input type="hidden" name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="postingan_id"
                               value="<?= (int) $postingan['id']; ?>">

                        <label>Tanggapan</label>

                        <textarea
                            name="isi"
                            maxlength="2000"
                            placeholder="Tulis jawaban, saran, atau tanggapanmu"
                            required
                        ></textarea>

                        <button type="submit" name="balas" value="1">
                            Kirim Tanggapan
                        </button>

                    </form>

                <?php endif; ?>

            </section>

        <?php else: ?>

            <!-- ===== DAFTAR POSTINGAN ===== -->

            <div class="topbar">
                <div>
                    <h1>Forum Diskusi</h1>
                    <p>Tanya jawab dan request materi antar mahasiswa
                        <?= e($profil['nama_prodi']); ?></p>
                </div>
            </div>

            <?php if ($pesan !== ''): ?>

                <div class="alert <?= $berhasil ? 'alert-success' : 'alert-error'; ?>">
                    <?= e($pesan); ?>
                </div>

            <?php endif; ?>

            <!-- Form buat postingan (dibuka sebagai pop-up oleh app.js) -->
            <section class="content-card" data-modal="Buat Postingan">

                <h2>Buat Postingan Baru</h2>

                <form method="POST">

                    <input type="hidden" name="csrf_token"
                           value="<?= e($_SESSION['csrf_token']); ?>">

                    <label>Kategori</label>

                    <select name="kategori" required>
                        <option value="">Pilih kategori</option>
                        <?php foreach ($kategoriList as $kode => $label): ?>
                            <option value="<?= e($kode); ?>"
                                <?= $lama['kategori'] === $kode ? 'selected' : ''; ?>>
                                <?= e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Mata Kuliah (opsional)</label>

                    <select name="mata_kuliah_id">
                        <option value="">Tidak terkait mata kuliah tertentu</option>
                        <?php foreach ($mataKuliahList as $mk): ?>
                            <option value="<?= (int) $mk['id']; ?>"
                                <?= $lama['mata_kuliah_id'] === (string) $mk['id'] ? 'selected' : ''; ?>>
                                Semester <?= (int) $mk['semester']; ?> -
                                <?= e($mk['nama_mk']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Judul</label>

                    <input type="text" name="judul" minlength="5" maxlength="150"
                           placeholder="Ringkas dan jelas"
                           value="<?= e($lama['judul']); ?>" required>

                    <label>Isi Postingan</label>

                    <textarea name="isi" minlength="10" maxlength="3000"
                              placeholder="Jelaskan pertanyaan, materi yang kamu cari, atau reviewmu"
                              required><?= e($lama['isi']); ?></textarea>

                    <button type="submit" name="buat_postingan" value="1">
                        Publikasikan
                    </button>

                </form>

            </section>

            <div class="arsip-box">

                <form method="GET" class="filter-bar">

                    <input type="text" name="q" maxlength="100"
                           placeholder="Cari judul atau isi postingan"
                           value="<?= e($q); ?>">

                    <select name="kategori">
                        <option value="">Semua kategori</option>
                        <?php foreach ($kategoriList as $kode => $label): ?>
                            <option value="<?= e($kode); ?>"
                                <?= $fKategori === $kode ? 'selected' : ''; ?>>
                                <?= e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status">
                        <option value="">Semua status</option>
                        <?php foreach ($statusList as $kode => $label): ?>
                            <option value="<?= e($kode); ?>"
                                <?= $fStatus === $kode ? 'selected' : ''; ?>>
                                <?= e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="mk">
                        <option value="">Semua mata kuliah</option>
                        <?php foreach ($mataKuliahList as $mk): ?>
                            <option value="<?= (int) $mk['id']; ?>"
                                <?= $fMk === (string) $mk['id'] ? 'selected' : ''; ?>>
                                <?= e($mk['nama_mk']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Cari</button>
                    <a href="diskusi.php" class="btn-secondary">Reset</a>

                </form>

            </div>

            <p class="hasil-info"><?= count($daftar); ?> postingan ditemukan</p>

            <?php if (count($daftar) > 0): ?>

                <div class="file-list">

                    <?php foreach ($daftar as $p): ?>

                        <a class="file-row"
                           href="diskusi.php?id=<?= (int) $p['id']; ?>">

                            <div class="file-icon"><?= icon('chat'); ?></div>

                            <div class="file-name">

                                <strong><?= e($p['judul']); ?></strong>

                                <small>
                                    <?= e($p['nama_penulis']); ?>
                                    &middot;
                                    <?= e(formatWaktu($p['dibuat_pada'])); ?>
                                    <?php if (!empty($p['nama_mk'])): ?>
                                        &middot; <?= e($p['nama_mk']); ?>
                                    <?php endif; ?>
                                    &middot; <?= (int) $p['jumlah_balasan']; ?> tanggapan
                                </small>

                            </div>

                            <span class="file-badge">
                                <?= e($kategoriList[$p['kategori']] ?? $p['kategori']); ?>
                            </span>

                            <span class="file-badge status-<?= e($p['status']); ?>">
                                <?= e($statusList[$p['status']]); ?>
                            </span>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">
                    <div><?= icon('chat'); ?></div>
                    <strong>Belum Ada Postingan</strong>
                    <p>
                        Mulai diskusi pertama atau ubah filter pencarianmu.
                    </p>
                </div>

            <?php endif; ?>

        <?php endif; ?>

    </main>

</div>

<?php if ($pesan !== '' && !$berhasil): ?>

    <script>
    // Buka kembali pop-up form bila pembuatan postingan gagal
    window.addEventListener('load', function () {
        const tombol = document.querySelector('.btn-open-modal');
        if (tombol) {
            tombol.click();
        }
    });
    </script>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
