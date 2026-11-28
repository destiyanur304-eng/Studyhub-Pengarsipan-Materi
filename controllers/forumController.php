<?php

require_once __DIR__ . '/../models/forum.php';

class ForumController
{
    private $forum;

    // Kategori postingan: kode di database => label tampilan
    const KATEGORI = [
        'tanya_jawab'    => 'Tanya Jawab / Konsultasi Materi',
        'request_materi' => 'Request Materi Baru',
        'review_materi'  => 'Review Materi'
    ];

    const STATUS = [
        'terbuka' => 'Terbuka',
        'selesai' => 'Terjawab / Selesai'
    ];

    public function __construct($pdo)
    {
        $this->forum = new Forum($pdo);
    }

    // Daftar kategori untuk form dan filter
    public function daftarKategori()
    {
        return self::KATEGORI;
    }

    // Daftar status untuk filter
    public function daftarStatus()
    {
        return self::STATUS;
    }

    // Mata kuliah program studi mahasiswa
    public function daftarMataKuliah($prodiId)
    {
        return $this->forum->getMataKuliahProdi($prodiId);
    }

    // Daftar postingan, dengan filter yang sudah divalidasi pemanggil
    public function index($prodiId, $filters)
    {
        return $this->forum->getDaftar($prodiId, $filters);
    }

    // Detail postingan; null bila tidak ada atau beda program studi
    public function show($id, $prodiId)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || $id <= 0) {
            return null;
        }

        $postingan = $this->forum->getById($id);

        if (
            !$postingan ||
            (int) $postingan['prodi_penulis'] !== (int) $prodiId
        ) {
            return null;
        }

        return $postingan;
    }

    // Balasan sebuah postingan
    public function balasan($postinganId)
    {
        return $this->forum->getBalasan($postinganId);
    }

    // Membuat postingan baru
    public function buat($mahasiswaId, $prodiId, $data)
    {
        $kategori = is_string($data['kategori'] ?? null)
            ? $data['kategori']
            : '';

        $judul = is_string($data['judul'] ?? null)
            ? trim($data['judul'])
            : '';

        $isi = is_string($data['isi'] ?? null)
            ? trim($data['isi'])
            : '';

        if (!array_key_exists($kategori, self::KATEGORI)) {
            return [
                'success' => false,
                'message' => 'Kategori postingan tidak valid.'
            ];
        }

        if (mb_strlen($judul) < 5 || mb_strlen($judul) > 150) {
            return [
                'success' => false,
                'message' => 'Judul harus 5 sampai 150 karakter.'
            ];
        }

        if (mb_strlen($isi) < 10 || mb_strlen($isi) > 3000) {
            return [
                'success' => false,
                'message' => 'Isi postingan harus 10 sampai 3000 karakter.'
            ];
        }

        // Mata kuliah bersifat opsional
        $mkId = null;
        $mkInput = $data['mata_kuliah_id'] ?? '';

        if (is_string($mkInput) && $mkInput !== '') {
            $mkId = filter_var($mkInput, FILTER_VALIDATE_INT);

            if (
                $mkId === false ||
                $mkId <= 0 ||
                !$this->forum->mataKuliahValid($mkId, $prodiId)
            ) {
                return [
                    'success' => false,
                    'message' => 'Mata kuliah yang dipilih tidak valid.'
                ];
            }
        }

        $id = $this->forum->create([
            'mahasiswa_id'   => $mahasiswaId,
            'mata_kuliah_id' => $mkId,
            'kategori'       => $kategori,
            'judul'          => $judul,
            'isi'            => $isi
        ]);

        return [
            'success' => true,
            'message' => 'Postingan berhasil dipublikasikan.',
            'id'      => $id
        ];
    }

    // Membalas postingan
    public function balas($postinganId, $mahasiswaId, $prodiId, $isi)
    {
        $postingan = $this->show($postinganId, $prodiId);

        if (!$postingan) {
            return [
                'success' => false,
                'message' => 'Postingan tidak ditemukan.'
            ];
        }

        if ($postingan['status'] === 'selesai') {
            return [
                'success' => false,
                'message' => 'Diskusi ini sudah ditandai selesai.'
            ];
        }

        $isi = is_string($isi) ? trim($isi) : '';

        if (mb_strlen($isi) < 2 || mb_strlen($isi) > 2000) {
            return [
                'success' => false,
                'message' => 'Tanggapan harus 2 sampai 2000 karakter.'
            ];
        }

        $this->forum->tambahBalasan($postingan['id'], $mahasiswaId, $isi);

        return [
            'success' => true,
            'message' => 'Tanggapanmu berhasil dikirim.'
        ];
    }

    // Menandai selesai atau membuka kembali (hanya pembuat postingan)
    public function ubahStatus($postinganId, $mahasiswaId, $prodiId, $status)
    {
        if (!array_key_exists($status, self::STATUS)) {
            return [
                'success' => false,
                'message' => 'Status tidak valid.'
            ];
        }

        $postingan = $this->show($postinganId, $prodiId);

        if (!$postingan) {
            return [
                'success' => false,
                'message' => 'Postingan tidak ditemukan.'
            ];
        }

        if ((int) $postingan['mahasiswa_id'] !== (int) $mahasiswaId) {
            return [
                'success' => false,
                'message' => 'Hanya pembuat postingan yang dapat mengubah status.'
            ];
        }

        $this->forum->setStatus($postingan['id'], $mahasiswaId, $status);

        return [
            'success' => true,
            'message' => $status === 'selesai'
                ? 'Diskusi ditandai sebagai terjawab / selesai.'
                : 'Diskusi dibuka kembali.'
        ];
    }

    // Menghapus postingan (hanya pembuat)
    public function hapusPostingan($postinganId, $mahasiswaId, $prodiId)
    {
        $postingan = $this->show($postinganId, $prodiId);

        if (
            !$postingan ||
            (int) $postingan['mahasiswa_id'] !== (int) $mahasiswaId
        ) {
            return [
                'success' => false,
                'message' => 'Postingan tidak dapat dihapus.'
            ];
        }

        $this->forum->hapusPostingan($postingan['id'], $mahasiswaId);

        return [
            'success' => true,
            'message' => 'Postingan berhasil dihapus.'
        ];
    }

    // Menghapus balasan (hanya pembuat balasan)
    public function hapusBalasan($balasanId, $postinganId, $mahasiswaId, $prodiId)
    {
        $postingan = $this->show($postinganId, $prodiId);

        if (
            !$postingan ||
            !filter_var($balasanId, FILTER_VALIDATE_INT) ||
            $balasanId <= 0
        ) {
            return [
                'success' => false,
                'message' => 'Tanggapan tidak ditemukan.'
            ];
        }

        $this->forum->hapusBalasan(
            $balasanId,
            $postingan['id'],
            $mahasiswaId
        );

        return [
            'success' => true,
            'message' => 'Tanggapan berhasil dihapus.'
        ];
    }
}
