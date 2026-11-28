<?php

require_once __DIR__ . '/../models/user.php';

class MahasiswaController
{
    private $user;

    public function __construct($pdo)
    {
        $this->user = new User($pdo);
    }

    // Menampilkan seluruh mahasiswa
    public function index()
    {
        return $this->user->getAllMahasiswa();
    }

    // Mengambil daftar program studi
    public function getProgramStudi()
    {
        return $this->user->getProgramStudi();
    }

    // Mengambil satu mahasiswa
    public function show($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            return null;
        }

        return $this->user->getById($id);
    }

    // Validasi data mahasiswa
    private function validasi($data, $passwordWajib = false)
    {
        $fieldWajib = [
            'nama',
            'nim',
            'username',
            'prodi_id',
            'semester'
        ];

        if ($passwordWajib) {
            $fieldWajib[] = 'password';
        }

        foreach ($fieldWajib as $field) {
            if (
                !isset($data[$field]) ||
                trim((string) $data[$field]) === ''
            ) {
                return [
                    'success' => false,
                    'message' => 'Semua data mahasiswa wajib diisi.'
                ];
            }
        }

        $nama = trim($data['nama']);
        $nim = trim($data['nim']);
        $username = trim($data['username']);
        $prodiId = $data['prodi_id'];
        $semester = $data['semester'];
        $password = trim($data['password'] ?? '');

        if (mb_strlen($nama) > 100) {
            return [
                'success' => false,
                'message' => 'Nama mahasiswa terlalu panjang.'
            ];
        }

        if (!preg_match('/^[0-9]+$/', $nim)) {
            return [
                'success' => false,
                'message' => 'NIM hanya boleh berisi angka.'
            ];
        }

        if (
            !filter_var($prodiId, FILTER_VALIDATE_INT) ||
            !filter_var($semester, FILTER_VALIDATE_INT)
        ) {
            return [
                'success' => false,
                'message' => 'Program studi atau semester tidak valid.'
            ];
        }

        if ((int) $prodiId <= 0 || (int) $semester < 1) {
            return [
                'success' => false,
                'message' => 'Program studi atau semester tidak valid.'
            ];
        }

        if ($passwordWajib && strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'Password minimal 8 karakter.'
            ];
        }

        if (!$passwordWajib && $password !== '' && strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'Password minimal 8 karakter.'
            ];
        }

        $maksSemester = $this->user->getJumlahSemesterProdi($prodiId);

        if ($maksSemester === false) {
            return [
                'success' => false,
                'message' => 'Program studi tidak ditemukan.'
            ];
        }

        if ((int) $semester > (int) $maksSemester) {
            return [
                'success' => false,
                'message' => 'Semester melebihi batas program studi.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Data mahasiswa valid.'
        ];
    }

    // Menambahkan mahasiswa
    public function store($data)
    {
        $hasilValidasi = $this->validasi($data, true);

        if (!$hasilValidasi['success']) {
            return $hasilValidasi;
        }

        try {
            $this->user->create($data);

            return [
                'success' => true,
                'message' => 'Mahasiswa berhasil ditambahkan.'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'NIM atau username sudah digunakan, atau data tidak valid.'
            ];
        }
    }

    // Mengubah data mahasiswa
    public function update($id, $data)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID mahasiswa tidak valid.'
            ];
        }

        if (!$this->show($id)) {
            return [
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan.'
            ];
        }

        $hasilValidasi = $this->validasi($data, false);

        if (!$hasilValidasi['success']) {
            return $hasilValidasi;
        }

        try {
            $this->user->update($id, $data);

            return [
                'success' => true,
                'message' => 'Data mahasiswa berhasil diperbarui.'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'NIM atau username sudah digunakan, atau data tidak valid.'
            ];
        }
    }

    // Menghapus mahasiswa
    public function destroy($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID mahasiswa tidak valid.'
            ];
        }

        if (!$this->show($id)) {
            return [
                'success' => false,
                'message' => 'Data mahasiswa tidak ditemukan.'
            ];
        }

        try {
            $this->user->delete($id);

            return [
                'success' => true,
                'message' => 'Mahasiswa berhasil dihapus.'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Mahasiswa gagal dihapus karena masih memiliki data yang terkait.'
            ];
        }
    }
}