<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/materiController.php';

requireRole('admin');

// Pastikan CSRF token tersedia
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$controller = new MateriController($pdo);

// Ambil kategori beserta ID
$kategoriList = $controller->getKategori();

$pesan = '';
$sukses = false;
$galat = [];

$isi = [
    'mata_kuliah_id' => '',
    'tahun_arsip'    => '2026',
    'kategori_id'    => $kategoriList[0]['id'] ?? '',
    'jenis_bawaan'   => 'PDF',
    'daftar'         => '',
];

$tahunList = ['2026', '2025', '2024', '2023'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
        !is_string($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Permintaan tidak valid.');
    }

    foreach ($isi as $kunci => $nilai) {
        if (isset($_POST[$kunci]) && is_string($_POST[$kunci])) {
            $isi[$kunci] = $_POST[$kunci];
        }
    }

    $hasil = $controller->storeBanyak($isi);

    $pesan = $hasil['message'];
    $sukses = $hasil['success'];
    $galat = $hasil['errors'] ?? [];

    // Kalau berhasil, kosongkan daftar agar tidak tersimpan dua kali
    if ($sukses) {
        $isi['daftar'] = '';
    }
}

// Mata kuliah dikelompokkan per program studi
$matakuliah = $controller->getMataKuliah();

usort($matakuliah, function ($a, $b) {
    return [(int) $a['semester'], $a['nama_mk']]
       <=> [(int) $b['semester'], $b['nama_mk']];
});

$grup = [];

foreach ($matakuliah as $mk) {
    $grup[$mk['nama_prodi']][] = $mk;
}

ksort($grup);

$pageTitle = 'Tambah Banyak Materi';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Tambah Banyak Materi</h1>
                <p>Masukkan banyak materi sekaligus dari daftar judul dan tautan Google Drive.</p>
            </div>

            <a href="materi.php" class="btn-secondary btn-lg">&larr; Kembali ke Data Materi</a>
        </div>

        <?php if ($pesan !== ''): ?>
            <div class="alert <?= $sukses ? 'alert-success' : 'alert-error'; ?>">
                <?= e($pesan); ?>

                <?php if (count($galat) > 0): ?>
                    <ul class="alert-list">
                        <?php foreach ($galat as $g): ?>
                            <li><?= e($g); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="content-card">

            <h2>Data Materi</h2>

            <form method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']); ?>">

                <label>Mata Kuliah</label>

                <select name="mata_kuliah_id" required>

                    <option value="">Pilih Mata Kuliah</option>

                    <?php foreach ($grup as $namaProdi => $daftarMk): ?>

                        <optgroup label="<?= e($namaProdi); ?>">

                            <?php foreach ($daftarMk as $mk): ?>
                                <option value="<?= e($mk['id']); ?>"
                                    <?= (string) $isi['mata_kuliah_id'] === (string) $mk['id'] ? 'selected' : ''; ?>>
                                    Semester <?= e($mk['semester']); ?> -
                                    <?= e($mk['nama_mk']); ?>
                                </option>
                            <?php endforeach; ?>

                        </optgroup>

                    <?php endforeach; ?>

                </select>

                <label>Tahun Arsip</label>

                <select name="tahun_arsip" required>
                    <?php foreach ($tahunList as $t): ?>
                        <option value="<?= e($t); ?>"
                            <?= $isi['tahun_arsip'] === $t ? 'selected' : ''; ?>>
                            <?= e($t); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Kategori</label>

                <select name="kategori_id" required>

                    <option value="">Pilih Kategori</option>

                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= e($k['id']); ?>"
                            <?= (string) $isi['kategori_id'] === (string) $k['id'] ? 'selected' : ''; ?>>
                            <?= e($k['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>

                </select>

                <label>Jenis Berkas Bawaan</label>

                <select name="jenis_bawaan" required>
                    <?php foreach (['PDF', 'PPT'] as $j): ?>
                        <option value="<?= $j; ?>"
                            <?= $isi['jenis_bawaan'] === $j ? 'selected' : ''; ?>>
                            <?= $j; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Daftar Materi</label>

                <textarea name="daftar"
                          class="daftar-materi"
                          placeholder="Modul 1 - HTML Dasar | https://drive.google.com/file/d/.../view"
                          required><?= e($isi['daftar']); ?></textarea>

                <p class="form-help">
                    Satu baris untuk satu materi. Maksimal 100 baris sekali simpan.
                </p>

                <button type="submit">Simpan Semua</button>

            </form>

        </section>

        <section class="content-card">

            <h2>Cara Mengisi</h2>

            <ol class="steps">
                <li>Pilih mata kuliah, tahun, dan kategori. Ketiganya berlaku untuk <strong>semua</strong> baris.</li>
                <li>Di Google Drive, klik kanan file, pilih <strong>Bagikan</strong>, atur ke <strong>Siapa saja yang memiliki link</strong>, lalu <strong>Salin link</strong>.</li>
                <li>Tulis satu baris per materi dengan format di bawah, lalu tempel semuanya di kotak Daftar Materi.</li>
            </ol>

            <pre class="contoh-daftar">
                Modul 1 - HTML Dasar | https://drive.google.com/file/d/1AbCdEfGhIjKlMn/view
                Modul 2 - CSS | https://drive.google.com/file/d/1OpQrStUvWxYz01/view
                Slide Pertemuan 3 | https://drive.google.com/file/d/1Zy9XwVuTsRqPoNm/view | PPT</pre>

            <p class="info-text">
                Bagian ketiga (<code>PDF</code> atau <code>PPT</code>) boleh dikosongkan.
                Kalau kosong, dipakai jenis berkas bawaan di atas. Jangan memakai
                tanda <code>|</code> di dalam judul. Tautan folder Drive tidak diterima,
                gunakan tautan file.
            </p>

            <p class="info-text">
                Penyimpanan bersifat <strong>semua atau tidak sama sekali</strong>:
                jika ada satu baris yang salah, tidak ada materi yang disimpan dan
                nomor baris yang bermasalah akan ditampilkan.
            </p>

        </section>

    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>