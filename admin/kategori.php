<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/kategoriController.php';

requireRole('admin');

$controller = new KategoriController($pdo);

$pesan = '';
$berhasil = false;
$editData = null;

// Proses tambah, edit, dan hapus kategori
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
    }

    if (isset($hasil)) {
        $pesan = $hasil['message'];
        $berhasil = $hasil['success'];
    }
}

// Mengambil data kategori untuk diedit
if (isset($_GET['edit'])) {
    $editData = $controller->getById($_GET['edit']);
}

// Mengambil seluruh data kategori
$kategori = $controller->index();

$pageTitle = 'Data Kategori';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Data Kategori</h1>
                <p>Kelola kategori materi perkuliahan.</p>
            </div>
        </div>

        <?php if ($pesan !== ''): ?>

            <div class="alert <?= $berhasil ? 'alert-success' : 'alert-error'; ?>">
                <?= e($pesan); ?>
            </div>

        <?php endif; ?>

        <!-- Form Tambah dan Edit Kategori -->
        <section class="content-card" data-modal="Tambah Kategori">

            <h2>
                <?= $editData ? 'Edit Kategori' : 'Tambah Kategori'; ?>
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

                <label>Nama Kategori</label>

                <input
                    type="text"
                    name="nama_kategori"
                    maxlength="100"
                    placeholder="Masukkan nama kategori"
                    value="<?= e($editData['nama_kategori'] ?? ''); ?>"
                    required
                >

                <label>Keterangan</label>

                <textarea
                    name="keterangan"
                    rows="4"
                    placeholder="Masukkan keterangan kategori (opsional)"
                ><?= e($editData['keterangan'] ?? ''); ?></textarea>

                <?php if ($editData): ?>

                    <button type="submit" name="ubah">
                        Simpan Perubahan
                    </button>

                    <a href="kategori.php" class="btn-secondary">
                        Batal
                    </a>

                <?php else: ?>

                    <button type="submit" name="tambah">
                        Tambah Kategori
                    </button>

                <?php endif; ?>

            </form>

        </section>

        <!-- Daftar Kategori -->
        <section class="content-card"
                 data-search="Cari nama kategori atau keterangan...">

            <h2>Daftar Kategori</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($kategori) > 0): ?>

                        <?php foreach ($kategori as $index => $k): ?>

                            <tr>

                                <td><?= $index + 1; ?></td>

                                <td><?= e($k['nama_kategori']); ?></td>

                                <td>
                                    <?= e($k['keterangan'] ?? '-'); ?>
                                </td>

                                <td>

                                    <div class="action-buttons">

                                        <a
                                            href="kategori.php?edit=<?= e($k['id']); ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus kategori ini? Jika masih digunakan oleh materi, penghapusan mungkin akan ditolak oleh database.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION['csrf_token']); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= e($k['id']); ?>"
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
                            <td colspan="4">
                                Belum ada data kategori.
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