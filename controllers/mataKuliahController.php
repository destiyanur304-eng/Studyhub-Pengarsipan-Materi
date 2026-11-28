<?php

require_once __DIR__ . '/../models/mataKuliah.php';

class MataKuliahController
{
    private $model;

    public function __construct($pdo)
    {
        $this->model = new MataKuliah($pdo);
    }

    public function index()
    {
        return $this->model->getAll();
    }

    public function getById($id)
    {
        return $this->model->getById($id);
    }

    public function getProgramStudi()
    {
        return $this->model->getProgramStudi();
    }

    private function validasi($data)
    {
        $nama = trim($data['nama_mk'] ?? '');
        $prodiId = $data['prodi_id'] ?? '';
        $semester = $data['semester'] ?? '';

        if (
            $nama === '' ||
            !filter_var($prodiId, FILTER_VALIDATE_INT) ||
            !filter_var($semester, FILTER_VALIDATE_INT)
        ) {
            return [
                'success' => false,
                'message' => 'Semua data mata kuliah wajib diisi dengan benar.'
            ];
        }

        if (mb_strlen($nama) > 150) {
            return [
                'success' => false,
                'message' => 'Nama mata kuliah maksimal 150 karakter.'
            ];
        }

        if ($prodiId <= 0 || $semester < 1) {
            return [
                'success' => false,
                'message' => 'Program studi atau semester tidak valid.'
            ];
        }

        $maksSemester = $this->model->getJumlahSemesterProdi($prodiId);

        if ($maksSemester === false) {
            return [
                'success' => false,
                'message' => 'Program studi tidak ditemukan.'
            ];
        }

        if ($semester > (int) $maksSemester) {
            return [
                'success' => false,
                'message' => 'Semester melebihi batas program studi.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Data valid.'
        ];
    }

    public function store($data)
    {
        $hasil = $this->validasi($data);

        if (!$hasil['success']) {
            return $hasil;
        }

        try {
            $this->model->create($data);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Mata kuliah gagal ditambahkan. Periksa kemungkinan data duplikat.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Mata kuliah berhasil ditambahkan.'
        ];
    }

    public function update($id, $data)
    {
        if (
            !filter_var($id, FILTER_VALIDATE_INT) ||
            $id <= 0 ||
            !$this->model->getById($id)
        ) {
            return [
                'success' => false,
                'message' => 'Data mata kuliah tidak ditemukan.'
            ];
        }

        $hasil = $this->validasi($data);

        if (!$hasil['success']) {
            return $hasil;
        }

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Mata kuliah gagal diperbarui. Periksa kemungkinan data duplikat.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Mata kuliah berhasil diperbarui.'
        ];
    }

    public function destroy($id)
    {
        if (
            !filter_var($id, FILTER_VALIDATE_INT) ||
            $id <= 0 ||
            !$this->model->getById($id)
        ) {
            return [
                'success' => false,
                'message' => 'Data mata kuliah tidak ditemukan.'
            ];
        }

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Mata kuliah gagal dihapus.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Mata kuliah berhasil dihapus.'
        ];
    }
}