<?php

require_once __DIR__ . '/../models/materi.php';

class MateriController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new Materi($pdo);
    }

    public function index()
    {
        return $this->model->getAll();
    }

    public function show($id)
    {
        return $this->model->getById($id);
    }

    public function getMataKuliah()
    {
        return $this->model->getMataKuliah();
    }

    public function getNamaKategori()
    {
        return $this->model->getNamaKategori();
    }

    public function getKategori()
    {
        return $this->model->getKategori();
    }

    public function store($data)
    {
        $hasil = $this->storeValidation($data);

        if (!$hasil['success']) {
            return $hasil;
        }

        $data['judul'] = trim($data['judul']);
        $data['deskripsi'] = trim($data['deskripsi'] ?? '');

        try {
            $this->model->create($data);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Materi gagal disimpan. Pastikan mata kuliah dan kategori masih tersedia.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Materi berhasil ditambahkan.'
        ];
    }

    // Tambah banyak materi sekaligus
    public function storeBanyak($data)
    {
        $kembali = function ($pesan, $galat = []) {
            return [
                'success' => false,
                'message' => $pesan,
                'errors' => $galat,
                'jumlah' => 0
            ];
        };

        $mkId = $data['mata_kuliah_id'] ?? '';
        $tahun = $data['tahun_arsip'] ?? '';
        $kategoriId = $data['kategori_id'] ?? '';
        $jenisBawaan = $data['jenis_bawaan'] ?? '';

        $daftar = (isset($data['daftar']) && is_string($data['daftar']))
            ? $data['daftar']
            : '';

        if (
            !filter_var($mkId, FILTER_VALIDATE_INT) ||
            (int) $mkId <= 0 ||
            !filter_var($tahun, FILTER_VALIDATE_INT) ||
            (int) $tahun < 2000 ||
            (int) $tahun > 2155
        ) {
            return $kembali('Mata kuliah atau tahun arsip tidak valid.');
        }

        if (
            !filter_var($kategoriId, FILTER_VALIDATE_INT) ||
            (int) $kategoriId <= 0 ||
            !$this->model->kategoriAda($kategoriId)
        ) {
            return $kembali('Kategori materi tidak valid.');
        }

        if (!in_array($jenisBawaan, ['PDF', 'PPT'], true)) {
            return $kembali('Jenis berkas bawaan tidak valid.');
        }

        if (trim($daftar) === '') {
            return $kembali('Daftar materi masih kosong.');
        }

        $cek = $this->pdo->prepare(
            "SELECT COUNT(*) FROM mata_kuliah WHERE id = ?"
        );
        $cek->execute([$mkId]);

        if ((int) $cek->fetchColumn() === 0) {
            return $kembali('Mata kuliah tidak ditemukan.');
        }

        $baris = preg_split('/\R/', $daftar);

        $terisi = 0;

        foreach ($baris as $teks) {
            if (trim($teks) !== '') {
                $terisi++;
            }
        }

        if ($terisi > 100) {
            return $kembali('Maksimal 100 materi dalam sekali simpan.');
        }

        $item = [];
        $galat = [];
        $dipakai = [];

        foreach ($baris as $i => $teks) {

            $teks = trim($teks);

            if ($teks === '') {
                continue;
            }

            $no = $i + 1;
            $bagian = array_map('trim', explode('|', $teks));

            if (count($bagian) < 2 || count($bagian) > 3) {
                $galat[] = 'Baris ' . $no . ': gunakan format Judul | Link (atau Judul | Link | PDF/PPT).';
                continue;
            }

            $judul = $bagian[0];
            $link = $bagian[1];

            $jenis = (isset($bagian[2]) && $bagian[2] !== '')
                ? strtoupper($bagian[2])
                : $jenisBawaan;

            if ($judul === '' || mb_strlen($judul) > 150) {
                $galat[] = 'Baris ' . $no . ': judul wajib diisi dan maksimal 150 karakter.';
                continue;
            }

            if (!in_array($jenis, ['PDF', 'PPT'], true)) {
                $galat[] = 'Baris ' . $no . ': jenis berkas harus PDF atau PPT.';
                continue;
            }

            if (!$this->linkDriveValid($link)) {
                $galat[] = 'Baris ' . $no . ': tautan Google Drive/Docs tidak valid.';
                continue;
            }

            $kunci = mb_strtolower($judul);

            if (isset($dipakai[$kunci])) {
                $galat[] = 'Baris ' . $no . ': judul sama dengan baris ' . $dipakai[$kunci] . '.';
                continue;
            }

            if ($this->model->judulAda($mkId, $tahun, $kategoriId, $judul)) {
                $galat[] = 'Baris ' . $no . ': materi dengan judul ini sudah ada pada mata kuliah, tahun, dan kategori yang sama.';
                continue;
            }

            $dipakai[$kunci] = $no;

            $item[] = [
                'judul' => $judul,
                'link' => $link,
                'jenis' => $jenis
            ];
        }

        if (count($galat) > 0) {
            return $kembali(
                'Ada baris yang perlu diperbaiki. Tidak ada materi yang disimpan.',
                $galat
            );
        }

        if (count($item) === 0) {
            return $kembali('Tidak ada materi yang dapat disimpan.');
        }

        try {
            $this->pdo->beginTransaction();

            foreach ($item as $m) {
                $this->model->create([
                    'judul' => $m['judul'],
                    'mata_kuliah_id' => (int) $mkId,
                    'tahun_arsip' => (int) $tahun,
                    'kategori_id' => (int) $kategoriId,
                    'jenis_berkas' => $m['jenis'],
                    'link_drive' => $m['link'],
                    'deskripsi' => ''
                ]);
            }

            $this->pdo->commit();

        } catch (PDOException $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return $kembali(
                'Terjadi kesalahan database. Tidak ada materi yang disimpan.'
            );
        }

        return [
            'success' => true,
            'message' => count($item) . ' materi berhasil ditambahkan.',
            'errors' => [],
            'jumlah' => count($item)
        ];
    }

    // Mengubah materi
    public function update($id, $data)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID materi tidak valid.'
            ];
        }

        if (!$this->model->getById($id)) {
            return [
                'success' => false,
                'message' => 'Materi tidak ditemukan.'
            ];
        }

        $hasil = $this->storeValidation($data);

        if (!$hasil['success']) {
            return $hasil;
        }

        $data['judul'] = trim($data['judul']);
        $data['deskripsi'] = trim($data['deskripsi'] ?? '');

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Materi gagal diperbarui. Pastikan mata kuliah dan kategori masih tersedia.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Materi berhasil diperbarui.'
        ];
    }

    // Validasi data tambah dan edit
    private function storeValidation($data)
    {
        $wajib = [
            'judul',
            'mata_kuliah_id',
            'tahun_arsip',
            'kategori_id',
            'jenis_berkas',
            'link_drive'
        ];

        foreach ($wajib as $field) {
            if (
                !isset($data[$field]) ||
                trim((string) $data[$field]) === ''
            ) {
                return [
                    'success' => false,
                    'message' => 'Semua data wajib diisi.'
                ];
            }
        }

        if (
            !filter_var($data['mata_kuliah_id'], FILTER_VALIDATE_INT) ||
            (int) $data['mata_kuliah_id'] <= 0 ||
            !filter_var($data['tahun_arsip'], FILTER_VALIDATE_INT) ||
            (int) $data['tahun_arsip'] < 2000 ||
            (int) $data['tahun_arsip'] > 2155
        ) {
            return [
                'success' => false,
                'message' => 'Mata kuliah atau tahun arsip tidak valid.'
            ];
        }

        if (
            !filter_var($data['kategori_id'], FILTER_VALIDATE_INT) ||
            (int) $data['kategori_id'] <= 0 ||
            !$this->model->kategoriAda($data['kategori_id'])
        ) {
            return [
                'success' => false,
                'message' => 'Kategori materi tidak valid.'
            ];
        }

        if (!in_array($data['jenis_berkas'], ['PDF', 'PPT'], true)) {
            return [
                'success' => false,
                'message' => 'Jenis berkas tidak valid.'
            ];
        }

        if (!$this->linkDriveValid($data['link_drive'])) {
            return [
                'success' => false,
                'message' => 'Tautan harus berupa tautan file Google Drive/Docs (bukan tautan folder).'
            ];
        }

        return [
            'success' => true,
            'message' => 'Valid'
        ];
    }

    // Validasi tautan Google Drive/Docs
    private function linkDriveValid($link)
    {
        if (!is_string($link) || !filter_var($link, FILTER_VALIDATE_URL)) {
            return false;
        }

        $bagian = parse_url($link);
        $scheme = strtolower($bagian['scheme'] ?? '');
        $host = strtolower($bagian['host'] ?? '');
        $path = $bagian['path'] ?? '';

        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if ($host === 'drive.google.com') {

            if (preg_match('#^/file/(?:u/\d+/)?d/[A-Za-z0-9_-]+#', $path)) {
                return true;
            }

            if ($path === '/open' || $path === '/uc') {
                parse_str($bagian['query'] ?? '', $q);

                return !empty($q['id'])
                    && is_string($q['id'])
                    && preg_match('/^[A-Za-z0-9_-]+$/', $q['id']);
            }

            return false;
        }

        if ($host === 'docs.google.com') {
            return (bool) preg_match(
                '#^/(document|presentation|spreadsheets)/d/[A-Za-z0-9_-]+#',
                $path
            );
        }

        return false;
    }

    // Menghapus materi
    public function destroy($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID materi tidak valid.'
            ];
        }

        if (!$this->model->getById($id)) {
            return [
                'success' => false,
                'message' => 'Materi tidak ditemukan.'
            ];
        }

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Materi gagal dihapus.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Materi berhasil dihapus.'
        ];
    }

    // Pencarian dan filter arsip
    public function search($filters)
    {
        return $this->model->search(
            trim($filters['keyword'] ?? ''),
            $filters['tahun'] ?? '',
            $filters['prodi'] ?? '',
            $filters['semester'] ?? '',
            $filters['kategori'] ?? ''
        );
    }
}