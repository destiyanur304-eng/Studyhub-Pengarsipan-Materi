<?php

require_once __DIR__ . '/../models/ulasan.php';

class UlasanController
{
    private $ulasan;

    public function __construct($pdo)
    {
        $this->ulasan = new Ulasan($pdo);
    }

    // Ringkasan rating sebuah materi
    public function ringkasan($materiId)
    {
        return $this->ulasan->getRingkasan($materiId);
    }

    // Sebaran rating per bintang
    public function distribusi($materiId)
    {
        return $this->ulasan->getDistribusi($materiId);
    }

    // Ulasan milik mahasiswa yang sedang login
    public function ulasanSaya($materiId, $mahasiswaId)
    {
        return $this->ulasan->getByMahasiswa($materiId, $mahasiswaId);
    }

    // Ulasan dari mahasiswa lain
    public function daftarLain($materiId, $mahasiswaId)
    {
        return $this->ulasan->getDaftarLain($materiId, $mahasiswaId);
    }

    // Menyimpan atau memperbarui ulasan
    public function simpan($materiId, $mahasiswaId, $data)
    {
        $rating = filter_var(
            $data['rating'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($rating === false || $rating < 1 || $rating > 5) {
            return [
                'success' => false,
                'message' => 'Pilih rating bintang antara 1 sampai 5.'
            ];
        }

        $komentar = isset($data['komentar']) && is_string($data['komentar'])
            ? trim($data['komentar'])
            : '';

        if (mb_strlen($komentar) > 1000) {
            return [
                'success' => false,
                'message' => 'Ulasan maksimal 1000 karakter.'
            ];
        }

        $sudahAda = (bool) $this->ulasan->getByMahasiswa(
            $materiId,
            $mahasiswaId
        );

        $this->ulasan->simpan(
            $materiId,
            $mahasiswaId,
            $rating,
            $komentar === '' ? null : $komentar
        );

        return [
            'success' => true,
            'message' => $sudahAda
                ? 'Ulasanmu berhasil diperbarui.'
                : 'Terima kasih, ulasanmu berhasil dikirim.'
        ];
    }

    // Menghapus ulasan milik sendiri
    public function hapus($materiId, $mahasiswaId)
    {
        $this->ulasan->hapus($materiId, $mahasiswaId);

        return [
            'success' => true,
            'message' => 'Ulasanmu berhasil dihapus.'
        ];
    }
}
