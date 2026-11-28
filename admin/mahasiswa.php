<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/mahasiswaController.php';

requireRole('admin');

$controller = new MahasiswaController($pdo);

// Token keamanan
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil pesan notifikasi
$pesan = $_SESSION['flash_message'] ?? '';
$berhasil = $_SESSION['flash_success'] ?? false;

unset($_SESSION['flash_message'], $_SESSION['flash_success']);

// Proses form
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
        $hasil = null;
    }

    if (isset($hasil)) {
        $_SESSION['flash_message'] = $hasil['message'];
        $_SESSION['flash_success'] = $hasil['success'];
    }

    // Kembali ke halaman utama agar pesan tampil
    header('Location: mahasiswa.php');
    exit;
}

// Data mahasiswa untuk diedit
$editData = null;

if (isset($_GET['edit'])) {
    $editData = $controller->show($_GET['edit']);

    if (!$editData) {
        $pesan = 'Data mahasiswa tidak ditemukan.';
        $berhasil = false;
    }
}

// Data tabel
$mahasiswa = $controller->index();
$prodi = $controller->getProgramStudi();

$pageTitle = 'Data Mahasiswa';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Data Mahasiswa</h1>
                <p>Kelola akun mahasiswa StudyHub.</p>
            </div>
        </div>

        <?php if ($pesan !== ''): ?>

            <div class="alert <?= $berhasil ? 'alert-success' : 'alert-error'; ?>">
                <?= e($pesan); ?>
            </div>

        <?php endif; ?>

        <!-- Form Tambah dan Edit -->
        <section class="content-card" data-modal="Tambah Mahasiswa">

            <h2>
                <?= $editData ? 'Edit Mahasiswa' : 'Tambah Mahasiswa'; ?>
            </h2>

            <form method="POST">

                <input type="hidden"
                       name="csrf_token"
                       value="<?= e($_SESSION['csrf_token']); ?>">

                <?php if ($editData): ?>

                    <input type="hidden"
                           name="id"
                           value="<?= e($editData['id']); ?>">

                <?php endif; ?>

                <label>Nama Lengkap</label>

                <input type="text"
                       name="nama"
                       placeholder="Masukkan nama lengkap"
                       maxlength="100"
                       value="<?= e($editData['nama'] ?? ''); ?>"
                       required>

                <label>NIM / NPM</label>

                <input type="text"
                       name="nim"
                       placeholder="Masukkan NIM atau NPM"
                       pattern="[0-9]+"
                       value="<?= e($editData['nim'] ?? ''); ?>"
                       required>

                <label>Username</label>

                <input type="text"
                       name="username"
                       placeholder="Masukkan username"
                       value="<?= e($editData['username'] ?? ''); ?>"
                       required>

                <label>
                    Password
                    <?= $editData ? '(kosongkan jika tidak diubah)' : ''; ?>
                </label>

                <input type="password"
                       name="password"
                       placeholder="<?= $editData
                           ? 'Masukkan password baru jika ingin mengubah'
                           : 'Minimal 8 karakter'; ?>"
                       minlength="8"
                       <?= $editData ? '' : 'required'; ?>>

                <label>Program Studi</label>

                <select name="prodi_id" id="prodi_id" required>

                    <option value="">Pilih Program Studi</option>

                    <?php foreach ($prodi as $p): ?>

                        <option
                            value="<?= e($p['id']); ?>"
                            data-semester="<?= e($p['jumlah_semester']); ?>"
                            <?= isset($editData['prodi_id']) &&
                                $editData['prodi_id'] == $p['id']
                                ? 'selected' : ''; ?>>

                            <?= e($p['nama_prodi']); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <label>Semester</label>

                <select name="semester" id="semester" required>
                    <option value="">Pilih Semester</option>
                </select>

                <?php if ($editData): ?>

                    <button type="submit" name="ubah">
                        Simpan Perubahan
                    </button>

                    <a href="mahasiswa.php" class="btn-secondary">
                        Batal
                    </a>

                <?php else: ?>

                    <button type="submit" name="tambah">
                        Tambah Mahasiswa
                    </button>

                <?php endif; ?>

            </form>

        </section>

        <!-- Tabel Mahasiswa -->
        <section class="content-card"
                 data-search="Cari nama, NIM, atau program studi...">

            <h2>Daftar Mahasiswa</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mahasiswa</th>
                            <th>NIM / NPM</th>
                            <th>Username</th>
                            <th>Program Studi</th>
                            <th>Semester</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($mahasiswa) > 0): ?>

                        <?php foreach ($mahasiswa as $index => $m): ?>

                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td><?= e($m['nama']); ?></td>
                                <td><?= e($m['nim']); ?></td>
                                <td><?= e($m['username']); ?></td>
                                <td><?= e($m['nama_prodi']); ?></td>
                                <td><?= e($m['semester']); ?></td>

                                <td>
                                    <div class="action-buttons">

                                        <a href="mahasiswa.php?edit=<?= e($m['id']); ?>"
                                           class="btn-edit">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus mahasiswa ini?');">

                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= e($_SESSION['csrf_token']); ?>">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= e($m['id']); ?>">

                                            <button type="submit"
                                                    name="hapus"
                                                    class="btn-danger">
                                                Hapus
                                            </button>

                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7">
                                Belum ada data mahasiswa.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

<script>
const prodiSelect = document.getElementById('prodi_id');
const semesterSelect = document.getElementById('semester');

const semesterAwal = <?= json_encode(
    $editData ? (int) $editData['semester'] : 0
); ?>;

function perbaruiSemester(nilaiTerpilih = 0) {

    const opsi = prodiSelect.options[prodiSelect.selectedIndex];

    const jumlahSemester = opsi
        ? parseInt(opsi.dataset.semester || '0', 10)
        : 0;

    semesterSelect.innerHTML =
        '<option value="">Pilih Semester</option>';

    for (let i = 1; i <= jumlahSemester; i++) {

        const option = document.createElement('option');

        option.value = i;
        option.textContent = 'Semester ' + i;

        if (i === parseInt(nilaiTerpilih, 10)) {
            option.selected = true;
        }

        semesterSelect.appendChild(option);
    }
}

prodiSelect.addEventListener('change', function () {
    perbaruiSemester(0);
});

perbaruiSemester(semesterAwal);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>