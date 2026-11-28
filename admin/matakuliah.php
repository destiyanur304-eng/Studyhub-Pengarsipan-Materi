<?php

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/mataKuliahController.php';

requireRole('admin');

$controller = new MataKuliahController($pdo);

$pesan = '';
$berhasil = false;
$editData = null;

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
    }

    if (isset($hasil)) {
        $pesan = $hasil['message'];
        $berhasil = $hasil['success'];
    }
}

// Mengambil data untuk diedit
if (isset($_GET['edit'])) {
    $editData = $controller->getById($_GET['edit']);
}

// Mengambil data mata kuliah dan program studi
$matakuliah = $controller->index();
$prodi = $controller->getProgramStudi();

$pageTitle = 'Data Mata Kuliah';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="app-container">

    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="main-content">

        <div class="topbar">
            <div>
                <h1>Data Mata Kuliah</h1>
                <p>Kelola daftar mata kuliah perkuliahan.</p>
            </div>
        </div>

        <?php if ($pesan !== ''): ?>

            <div class="alert <?= $berhasil ? 'alert-success' : 'alert-error'; ?>">
                <?= e($pesan); ?>
            </div>

        <?php endif; ?>

        <!-- Form Tambah dan Edit dalam Pop-up -->
        <section class="content-card" data-modal="Tambah Mata Kuliah">

            <h2>
                <?= $editData ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah'; ?>
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

                <label>Nama Mata Kuliah</label>

                <input type="text"
                       name="nama_mk"
                       maxlength="150"
                       placeholder="Masukkan nama mata kuliah"
                       value="<?= e($editData['nama_mk'] ?? ''); ?>"
                       required>

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

                    <a href="matakuliah.php" class="btn-secondary">
                        Batal
                    </a>

                <?php else: ?>

                    <button type="submit" name="tambah">
                        Tambah Mata Kuliah
                    </button>

                <?php endif; ?>

            </form>

        </section>

        <!-- Daftar Mata Kuliah -->
        <section class="content-card"
                 data-search="Cari mata kuliah atau program studi...">

            <h2>Daftar Mata Kuliah</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Program Studi</th>
                            <th>Semester</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($matakuliah) > 0): ?>

                        <?php foreach ($matakuliah as $index => $mk): ?>

                            <tr>

                                <td><?= $index + 1; ?></td>

                                <td><?= e($mk['nama_mk']); ?></td>

                                <td><?= e($mk['nama_prodi']); ?></td>

                                <td><?= e($mk['semester']); ?></td>

                                <td>

                                    <div class="action-buttons">

                                        <a href="matakuliah.php?edit=<?= e($mk['id']); ?>"
                                           class="btn-edit">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus mata kuliah ini? Materi yang terkait juga akan terhapus.');">

                                            <input type="hidden"
                                                   name="csrf_token"
                                                   value="<?= e($_SESSION['csrf_token']); ?>">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= e($mk['id']); ?>">

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
                            <td colspan="5">
                                Belum ada data mata kuliah.
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