<?php

require_once __DIR__ . '/../models/kategori.php';

class KategoriController
{
    private $kategori;

    public function __construct($pdo)
    {
        $this->kategori = new Kategori($pdo);
    }

    // Mengambil seluruh data kategori
    public function index()
    {
        return $this->kategori->getAll();
    }

    // Mengambil kategori berdasarkan ID
    public function getById($id)
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            return null;
        }

        return $this->kategori->getById((int) $id);
    }

    // Validasi data kategori
    private function validasi($data, $id = null)
    {
        $nama = trim($data['nama_kategori'] ?? '');
        $keterangan = trim($data['keterangan'] ?? '');

        if ($nama === '') {
            return [
                'success' => false,
                'message' => 'Nama kategori wajib diisi.'
            ];
        }

        if (mb_strlen($nama) > 100) {
            return [
                'success' => false,
                'message' => 'Nama kategori maksimal 100 karakter.'
            ];
        }

        if ($this->kategori->namaSudahAda($nama, $id)) {
            return [
                'success' => false,
                'message' => 'Nama kategori sudah tersedia.'
            ];
        }

        return [
            'success' => true,
            'data' => [
                'nama_kategori' => $nama,
                'keterangan' => $keterangan
            ]
        ];
    }

    // Proses tambah kategori
    public function store($data)
    {
        $validasi = $this->validasi($data);

        if (!$validasi['success']) {
            return $validasi;
        }

        try {
            $hasil = $this->kategori->create($validasi['data']);

            return [
                'success' => $hasil,
                'message' => $hasil
                    ? 'Kategori berhasil ditambahkan.'
                    : 'Kategori gagal ditambahkan.'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Kategori gagal ditambahkan. Periksa kembali data kategori.'
            ];
        }
    }

    // Proses edit kategori
    public function update($id, $data)
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID kategori tidak valid.'
            ];
        }

        $id = (int) $id;

        if (!$this->kategori->getById($id)) {
            return [
                'success' => false,
                'message' => 'Data kategori tidak ditemukan.'
            ];
        }

        $validasi = $this->validasi($data, $id);

        if (!$validasi['success']) {
            return $validasi;
        }

        try {
            $hasil = $this->kategori->update($id, $validasi['data']);

            return [
                'success' => $hasil,
                'message' => $hasil
                    ? 'Kategori berhasil diperbarui.'
                    : 'Kategori gagal diperbarui.'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Kategori gagal diperbarui. Periksa kembali data kategori.'
            ];
        }
    }

    // Proses hapus kategori
    public function destroy($id)
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            return [
                'success' => false,
                'message' => 'ID kategori tidak valid.'
            ];
        }

        $id = (int) $id;

        if (!$this->kategori->getById($id)) {
            return [
                'success' => false,
                'message' => 'Data kategori tidak ditemukan.'
            ];
        }

        try {
            $hasil = $this->kategori->delete($id);

            return [
                'success' => $hasil,
                'message' => $hasil
                    ? 'Kategori berhasil dihapus.'
                    : 'Kategori gagal dihapus.'
            ];

        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh data materi.'
            ];
        }
    }
}