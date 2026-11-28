<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../controllers/materiController.php';

requireRole('admin');

$controller = new MateriController($pdo);

// Membuat token CSRF jika belum tersedia
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil notifikasi dari session
$pesan = $_SESSION['flash_message'] ?? '';
$berhasil = $_SESSION['flash_success'] ?? false;

unset($_SESSION['flash_message'], $_SESSION['flash_success']);

// Proses tambah, edit, dan hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die('Permintaan tidak valid.');
    }

    if (isset($_POST['tambah'])) {

        $hasil = $controller->store($_POST);

    } elseif (isset($_POST['ubah'])) {

        $hasil = $controller->update($_POST['id'] ?? 0, $_POST);

    } elseif (isset($_POST['hapus'])) {

        $hasil = $controller->destroy($_POST['id'] ?? 0);

    } else {
        $hasil = [
            'success' => false,
            'message' => 'Permintaan tidak dikenali.'
        ];
    }

    // Menyimpan notifikasi untuk ditampilkan setelah redirect
    $_SESSION['flash_message'] = $hasil['message'] ?? 'Proses selesai.';
    $_SESSION['flash_success'] = $hasil['success'] ?? false;

    header('Location: materi.php');
    exit;
}

// Mengambil data untuk diedit
$editData = null;

if (isset($_GET['edit'])) {
    $editData = $controller->show($_GET['edit']);
}

// Mengambil data materi dan mata kuliah
$materi = $controller->index();
$matakuliah = $controller->getMataKuliah();

// Mengambil kategori langsung dari tabel kategori
$stmtKategori = $pdo->query(
    "SELECT id, nama_kategori
     FROM kategori
     ORDER BY nama_kategori ASC"
);

$kategoriList = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Data Materi';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Data Materi</h1>
                <p>Kelola arsip materi perkuliahan StudyHub.</p>
            </div>

            <a href="materi_massal.php" class="btn-primary btn-lg">
                Tambah Banyak Sekaligus
            </a>
        </div>

        <?php if ($pesan !== ''): ?>
            <div class="alert <?= $berhasil ? 'alert-success' : 'alert-error'; ?>">
                <?= e($pesan); ?>
            </div>
        <?php endif; ?>

        <section class="content-card" data-modal="Tambah Materi">

            <h2>
                <?= $editData ? 'Edit Materi' : 'Tambah Materi'; ?>
            </h2>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION['csrf_token']); ?>"
                >

                <?php if ($editData): ?>
                    <input
                        type="hidden"
                        name="id"
                        value="<?= e($editData['id']); ?>"
                    >
                <?php endif; ?>

                <label>Judul Materi</label>

                <input
                    type="text"
                    name="judul"
                    placeholder="Masukkan judul materi"
                    value="<?= e($editData['judul'] ?? ''); ?>"
                    required
                >

                <label>Mata Kuliah</label>

                <select name="mata_kuliah_id" required>

                    <option value="">Pilih Mata Kuliah</option>

                    <?php foreach ($matakuliah as $mk): ?>

                        <option
                            value="<?= e($mk['id']); ?>"
                            <?= isset($editData['mata_kuliah_id']) &&
                                $editData['mata_kuliah_id'] == $mk['id']
                                ? 'selected' : ''; ?>
                        >
                            <?= e($mk['nama_mk']); ?>
                            - <?= e($mk['nama_prodi']); ?>
                            (Semester <?= e($mk['semester']); ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

                <label>Tahun Arsip</label>

                <input
                    type="number"
                    name="tahun_arsip"
                    min="2000"
                    max="2155"
                    placeholder="Contoh: 2026"
                    value="<?= e($editData['tahun_arsip'] ?? date('Y')); ?>"
                    required
                >

                <label>Kategori Materi</label>

                <select name="kategori_id" required>

                    <option value="">Pilih Kategori</option>

                    <?php foreach ($kategoriList as $k): ?>

                        <option
                            value="<?= e($k['id']); ?>"
                            <?= isset($editData['kategori_id']) &&
                                $editData['kategori_id'] == $k['id']
                                ? 'selected' : ''; ?>
                        >
                            <?= e($k['nama_kategori']); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <label>Jenis Berkas</label>

                <select name="jenis_berkas" required>

                    <option value="">Pilih Jenis Berkas</option>

                    <option
                        value="PDF"
                        <?= ($editData['jenis_berkas'] ?? '') === 'PDF'
                            ? 'selected' : ''; ?>
                    >
                        PDF
                    </option>

                    <option
                        value="PPT"
                        <?= ($editData['jenis_berkas'] ?? '') === 'PPT'
                            ? 'selected' : ''; ?>
                    >
                        PPT
                    </option>

                </select>

                <label>Link Google Drive</label>

                <input
                    type="url"
                    name="link_drive"
                    placeholder="Masukkan link file Google Drive"
                    value="<?= e($editData['link_drive'] ?? ''); ?>"
                    required
                >

                <label>Deskripsi Materi</label>

                <textarea
                    name="deskripsi"
                    rows="4"
                    placeholder="Masukkan deskripsi materi"
                ><?= e($editData['deskripsi'] ?? ''); ?></textarea>

                <?php if ($editData): ?>

                    <button type="submit" name="ubah">
                        Simpan Perubahan
                    </button>

                    <a href="materi.php" class="btn-secondary">
                        Batal
                    </a>

                <?php else: ?>

                    <button type="submit" name="tambah">
                        Tambah Materi
                    </button>

                <?php endif; ?>

            </form>

        </section>

        <section class="content-card" data-search="Cari judul materi atau mata kuliah...">

            <h2>Daftar Materi Perkuliahan</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Materi</th>
                            <th>Mata Kuliah</th>
                            <th>Program Studi</th>
                            <th>Semester</th>
                            <th>Tahun</th>
                            <th>Kategori</th>
                            <th>Berkas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($materi) > 0): ?>

                        <?php foreach ($materi as $index => $m): ?>

                            <tr>

                                <td><?= $index + 1; ?></td>

                                <td><?= e($m['judul']); ?></td>

                                <td><?= e($m['nama_mk']); ?></td>

                                <td><?= e($m['nama_prodi']); ?></td>

                                <td><?= e($m['semester']); ?></td>

                                <td><?= e($m['tahun_arsip']); ?></td>

                                <td><?= e($m['kategori']); ?></td>

                                <td><?= e($m['jenis_berkas']); ?></td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="materi.php?edit=<?= e($m['id']); ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus materi ini?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION['csrf_token']); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= e($m['id']); ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="hapus"
                                                class="btn-danger"
                                            >
                                                Hapus
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9">
                                Belum ada data materi.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>