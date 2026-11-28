<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/arsipController.php';

requireRole('mahasiswa');

// Mengambil parameter URL sebagai teks (menolak array)
function getParam($key)
{
    return (isset($_GET[$key]) && is_string($_GET[$key]))
        ? trim($_GET[$key])
        : '';
}

$ctrl = new ArsipController($pdo);
$profil = $ctrl->profil($_SESSION['user']['id']);

if (!$profil) {
    die('Data program studi akunmu belum lengkap. Hubungi admin.');
}

$prodiId = (int) $profil['prodi_id'];
$jumlahSemester = $ctrl->jumlahSemester($prodiId);

// Daftar tahun: 2023-2026 ditambah tahun lain yang ada di database
$ringkasanTahun = $ctrl->ringkasanTahun($prodiId);
$tahunList = array_map(
    'strval',
    array_unique(array_merge(['2023', '2024', '2025', '2026'], array_keys($ringkasanTahun)))
);
rsort($tahunList);

$kategoriValid = $ctrl->daftarKategori();

$mode = (getParam('mode') === 'cari' || isset($_GET['keyword']))
    ? 'cari'
    : 'jelajah';

$thn = '';
$smt = 0;
$mk = null;
$q = '';
$kat = '';
$ringkasanSemester = [];
$daftarMk = [];
$berkas = [];
$hasil = [];
$filters = ['keyword' => '', 'tahun' => '', 'semester' => '', 'kategori' => ''];

if ($mode === 'jelajah') {

    // Tahun: harus salah satu dari daftar tahun
    if (in_array(getParam('thn'), $tahunList, true)) {
        $thn = getParam('thn');
    }

    // Semester: angka dalam rentang semester prodi
    if ($thn !== '') {
        $smtReq = filter_var(getParam('smt'), FILTER_VALIDATE_INT);

        if ($smtReq !== false && $smtReq >= 1 && $smtReq <= $jumlahSemester) {
            $smt = $smtReq;
        }
    }

    // Mata kuliah: harus milik prodi dan semester tersebut
    if ($smt > 0) {
        $mkReq = filter_var(getParam('mk'), FILTER_VALIDATE_INT);

        if ($mkReq !== false && $mkReq > 0) {
            $mk = $ctrl->mataKuliahValid($mkReq, $prodiId, $smt) ?: null;
        }
    }

    if ($thn === '') {
        // Tahap 1: pilih tahun (data sudah diambil di atas)

    } elseif ($smt === 0) {
        $ringkasanSemester = $ctrl->ringkasanSemester($prodiId, $thn);

    } elseif (!$mk) {
        $daftarMk = $ctrl->mataKuliahTahunSemester($prodiId, $thn, $smt);

    } else {
        $q = mb_substr(getParam('q'), 0, 100);

        if (in_array(getParam('kat'), $kategoriValid, true)) {
            $kat = getParam('kat');
        }

        $berkas = $ctrl->berkasMataKuliah($mk['id'], $thn, [
            'keyword'  => $q,
            'kategori' => $kat,
        ]);
    }

} else {

    $filters['keyword'] = mb_substr(getParam('keyword'), 0, 100);

    if (in_array(getParam('tahun'), $tahunList, true)) {
        $filters['tahun'] = getParam('tahun');
    }

    $smtReq = filter_var(getParam('semester'), FILTER_VALIDATE_INT);
    if ($smtReq !== false && $smtReq >= 1 && $smtReq <= $jumlahSemester) {
        $filters['semester'] = (string) $smtReq;
    }

    if (in_array(getParam('kategori'), $kategoriValid, true)) {
        $filters['kategori'] = getParam('kategori');
    }

    $hasil = $ctrl->cari($prodiId, $filters);
}

// Jejak folder (breadcrumb)
$crumbs = [[e($profil['nama_prodi']), 'arsip.php']];

if ($thn !== '') {
    $crumbs[] = ['Tahun ' . $thn, 'arsip.php?thn=' . $thn];
}
if ($smt > 0) {
    $crumbs[] = ['Semester ' . $smt, 'arsip.php?thn=' . $thn . '&smt=' . $smt];
}
if ($mk) {
    $crumbs[] = [e($mk['nama_mk']), null];
}

$pageTitle = 'Arsip Materi';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Arsip Materi</h1>
                <p><?= e($profil['nama_prodi']); ?></p>
            </div>
        </div>

        <div class="arsip-tabs">
            <a href="arsip.php"
               class="arsip-tab <?= $mode === 'jelajah' ? 'active' : ''; ?>">
                <?= icon('folder'); ?> Jelajahi folder
            </a>
            <a href="arsip.php?mode=cari"
               class="arsip-tab <?= $mode === 'cari' ? 'active' : ''; ?>">
                <?= icon('search'); ?> Cari dan filter
            </a>
        </div>

        <?php if ($mode === 'jelajah'): ?>

            <nav class="crumb" aria-label="Jejak folder">
                <?php foreach ($crumbs as $i => $c): ?>
                    <?php if ($i > 0): ?><span>/</span><?php endif; ?>
                    <?php if ($c[1] !== null && $i < count($crumbs) - 1): ?>
                        <a href="<?= $c[1]; ?>"><?= $c[0]; ?></a>
                    <?php else: ?>
                        <b><?= $c[0]; ?></b>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <?php if ($thn === ''): ?>

                <div class="folder-grid">
                    <?php foreach ($tahunList as $t): ?>
                        <a class="folder-card" href="arsip.php?thn=<?= e($t); ?>">
                            <div class="folder-icon"><?= icon('calendar'); ?></div>
                            <div class="folder-info">
                                <strong>Tahun <?= e($t); ?></strong>
                                <small>
                                    <?= (int) ($ringkasanTahun[$t]['jumlah_materi'] ?? 0); ?> materi
                                </small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($smt === 0): ?>

                <div class="folder-grid">
                    <?php for ($s = 1; $s <= $jumlahSemester; $s++): ?>
                        <?php $info = $ringkasanSemester[$s] ?? null; ?>
                        <a class="folder-card"
                           href="arsip.php?thn=<?= e($thn); ?>&smt=<?= $s; ?>">
                            <div class="folder-icon"><?= icon('folder'); ?></div>
                            <div class="folder-info">
                                <strong>Semester <?= $s; ?></strong>
                                <small>
                                    <?= (int) ($info['jumlah_mk'] ?? 0); ?> mata kuliah &middot;
                                    <?= (int) ($info['jumlah_materi'] ?? 0); ?> materi
                                </small>
                            </div>
                        </a>
                    <?php endfor; ?>
                </div>

            <?php elseif (!$mk): ?>

                <?php if (count($daftarMk) > 0): ?>
                    <div class="folder-grid">
                        <?php foreach ($daftarMk as $m): ?>
                            <a class="folder-card"
                               href="arsip.php?thn=<?= e($thn); ?>&smt=<?= $smt; ?>&mk=<?= e($m['id']); ?>">
                                <div class="folder-icon"><?= icon('book'); ?></div>
                                <div class="folder-info">
                                    <strong><?= e($m['nama_mk']); ?></strong>
                                    <small><?= (int) $m['jumlah_materi']; ?> materi</small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        Belum ada materi di semester ini pada tahun <?= e($thn); ?>.
                    </div>
                <?php endif; ?>

            <?php else: ?>

                <div class="arsip-box">
                    <form method="GET" class="filter-bar">
                        <input type="hidden" name="thn" value="<?= e($thn); ?>">
                        <input type="hidden" name="smt" value="<?= $smt; ?>">
                        <input type="hidden" name="mk" value="<?= e($mk['id']); ?>">

                        <input type="text" name="q" maxlength="100"
                               placeholder="Cari nama materi"
                               value="<?= e($q); ?>">

                        <select name="kat">
                            <option value="">Semua kategori</option>
                            <?php foreach ($kategoriValid as $k): ?>
                                <option value="<?= e($k); ?>" <?= $kat === $k ? 'selected' : ''; ?>>
                                    <?= e($k); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit">Cari</button>
                        <a href="arsip.php?thn=<?= e($thn); ?>&smt=<?= $smt; ?>&mk=<?= e($mk['id']); ?>"
                           class="btn-secondary">Reset</a>
                    </form>
                </div>

                <p class="hasil-info"><?= count($berkas); ?> materi ditemukan</p>

                <?php if (count($berkas) > 0): ?>
                    <div class="file-list">
                        <?php foreach ($berkas as $b): ?>
                            <a class="file-row"
                               href="buka.php?id=<?= e($b['id']); ?>">
                                <div class="file-icon"><?= icon('file'); ?></div>
                                <div class="file-name">
                                    <strong><?= e($b['judul']); ?></strong>
                                    <?php if (!empty($b['deskripsi'])): ?>
                                        <small><?= e($b['deskripsi']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="file-rating">
                                    <?php if (!empty($b['jumlah_ulasan'])): ?>
                                        <span class="star on">&#9733;</span>
                                        <b><?= e(formatRating($b['rata_rating'])); ?></b>
                                        <small>(<?= (int) $b['jumlah_ulasan']; ?>)</small>
                                    <?php else: ?>
                                        <small>Belum ada rating</small>
                                    <?php endif; ?>
                                </span>
                                <span class="file-badge"><?= e($b['kategori']); ?></span>
                                <span class="file-badge"><?= e($b['jenis_berkas']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">Materi tidak ditemukan.</div>
                <?php endif; ?>

            <?php endif; ?>

        <?php else: ?>

            <div class="arsip-box">
                <form method="GET" class="filter-bar">
                    <input type="hidden" name="mode" value="cari">

                    <input type="text" name="keyword" maxlength="100"
                           placeholder="Cari judul materi atau mata kuliah"
                           value="<?= e($filters['keyword']); ?>">

                    <select name="tahun">
                        <option value="">Semua tahun</option>
                        <?php foreach ($tahunList as $t): ?>
                            <option value="<?= e($t); ?>"
                                <?= $filters['tahun'] === $t ? 'selected' : ''; ?>>
                                <?= e($t); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="semester">
                        <option value="">Semua semester</option>
                        <?php for ($s = 1; $s <= $jumlahSemester; $s++): ?>
                            <option value="<?= $s; ?>"
                                <?= $filters['semester'] === (string) $s ? 'selected' : ''; ?>>
                                Semester <?= $s; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <select name="kategori">
                        <option value="">Semua kategori</option>
                        <?php foreach ($kategoriValid as $k): ?>
                            <option value="<?= e($k); ?>"
                                <?= $filters['kategori'] === $k ? 'selected' : ''; ?>>
                                <?= e($k); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Cari</button>
                    <a href="arsip.php?mode=cari" class="btn-secondary">Reset</a>
                </form>
            </div>

            <p class="hasil-info"><?= count($hasil); ?> materi ditemukan</p>

            <?php if (count($hasil) > 0): ?>
                <div class="file-list">
                    <?php foreach ($hasil as $b): ?>
                        <a class="file-row"
                           href="buka.php?id=<?= e($b['id']); ?>">
                            <div class="file-icon"><?= icon('file'); ?></div>
                            <div class="file-name">
                                <strong><?= e($b['judul']); ?></strong>
                                <small>
                                    <?= e($b['nama_mk']); ?> &middot;
                                    Semester <?= e($b['semester']); ?> &middot;
                                    Tahun <?= e($b['tahun_arsip']); ?>
                                </small>
                            </div>
                            <span class="file-rating">
                                <?php if (!empty($b['jumlah_ulasan'])): ?>
                                    <span class="star on">&#9733;</span>
                                    <b><?= e(formatRating($b['rata_rating'])); ?></b>
                                    <small>(<?= (int) $b['jumlah_ulasan']; ?>)</small>
                                <?php else: ?>
                                    <small>Belum ada rating</small>
                                <?php endif; ?>
                            </span>
                            <span class="file-badge"><?= e($b['kategori']); ?></span>
                            <span class="file-badge"><?= e($b['jenis_berkas']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">Materi tidak ditemukan.</div>
            <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>