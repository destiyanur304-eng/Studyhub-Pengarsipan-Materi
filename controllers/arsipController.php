<?php

require_once __DIR__ . '/../models/materi.php';

class ArsipController
{
    private $pdo;
    private $materi;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->materi = new Materi($pdo);
    }

    // Mengambil profil mahasiswa dari database
    public function profil($userId)
    {
        $query = $this->pdo->prepare(
            "SELECT
                mahasiswa.id,
                mahasiswa.nama,
                mahasiswa.nim,
                mahasiswa.username,
                mahasiswa.prodi_id,
                mahasiswa.semester,
                program_studi.nama_prodi
             FROM mahasiswa
             JOIN program_studi
                ON mahasiswa.prodi_id = program_studi.id
             WHERE mahasiswa.id = ?
             LIMIT 1"
        );

        $query->execute([$userId]);

        return $query->fetch();
    }

    // Mengambil jumlah semester program studi
    public function jumlahSemester($prodiId)
    {
        return $this->materi->getJumlahSemester($prodiId);
    }

    // Mengambil ringkasan materi berdasarkan tahun
    public function ringkasanTahun($prodiId)
    {
        return $this->materi->getRingkasanTahun($prodiId);
    }

    // Mengambil ringkasan semester berdasarkan tahun
    public function ringkasanSemester($prodiId, $tahun)
    {
        return $this->materi->getRingkasanSemesterTahun(
            $prodiId,
            $tahun
        );
    }

    // Mengambil mata kuliah berdasarkan tahun dan semester
    public function mataKuliahTahunSemester($prodiId, $tahun, $semester)
    {
        return $this->materi->getMataKuliahTahunSemester(
            $prodiId,
            $tahun,
            $semester
        );
    }

    // Memastikan mata kuliah sesuai dengan prodi dan semester
    public function mataKuliahValid($mkId, $prodiId, $semester)
    {
        return $this->materi->getMataKuliahValid(
            $mkId,
            $prodiId,
            $semester
        );
    }

    // Mengambil materi dalam folder mata kuliah
    public function berkasMataKuliah($mkId, $tahun, $filters)
    {
        return $this->materi->getBerkasMataKuliah(
            $mkId,
            $tahun,
            $filters
        );
    }

    // Melakukan pencarian materi mahasiswa
    public function cari($prodiId, $filters)
    {
        return $this->materi->cariMahasiswa(
            $prodiId,
            $filters
        );
    }

    // Mengambil daftar kategori materi
    public function daftarKategori()
    {
        return $this->materi->getNamaKategori();
    }

    // Mengambil detail materi berdasarkan ID
    public function materiUntukAkses($id)
    {
        return $this->materi->getById($id);
    }

    // Memeriksa hak akses mahasiswa terhadap materi
    public function bolehAkses($profil, $materi)
    {
        if (!$profil || !$materi) {
            return false;
        }

        return (int) $materi['prodi_id'] === (int) $profil['prodi_id'];
    }

    // Mencatat akses mahasiswa terhadap materi
    // Trigger database otomatis menambah jumlah_dilihat
    public function catatAkses($mahasiswaId, $materiId)
    {
        $query = $this->pdo->prepare(
            "INSERT INTO riwayat_akses
                (mahasiswa_id, materi_id)
             VALUES (?, ?)"
        );

        return $query->execute([
            $mahasiswaId,
            $materiId
        ]);
    }

    // Mengambil riwayat akses materi mahasiswa
    public function riwayatAkses($mahasiswaId, $prodiId)
    {
        $query = $this->pdo->prepare(
            "SELECT
                materi.id AS materi_id,
                materi.judul,
                materi.tahun_arsip,
                mata_kuliah.nama_mk,
                mata_kuliah.semester,
                kategori.nama_kategori,
                MAX(riwayat_akses.waktu_akses) AS waktu_terakhir
             FROM riwayat_akses
             JOIN materi
                ON riwayat_akses.materi_id = materi.id
             JOIN mata_kuliah
                ON materi.mata_kuliah_id = mata_kuliah.id
             JOIN kategori
                ON materi.kategori_id = kategori.id
             WHERE riwayat_akses.mahasiswa_id = ?
               AND mata_kuliah.prodi_id = ?
             GROUP BY
                materi.id,
                materi.judul,
                materi.tahun_arsip,
                mata_kuliah.nama_mk,
                mata_kuliah.semester,
                kategori.nama_kategori
             ORDER BY waktu_terakhir DESC"
        );

        $query->execute([
            $mahasiswaId,
            $prodiId
        ]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengenali tautan Google Drive dan Google Docs
    public function infoBerkas($link)
    {
        $bagian = parse_url(trim($link));

        if (
            !$bagian ||
            empty($bagian['host']) ||
            empty($bagian['scheme'])
        ) {
            return null;
        }

        if (!in_array(strtolower($bagian['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($bagian['host']);
        $path = $bagian['path'] ?? '';

        if ($host === 'drive.google.com') {

            // Format /file/d/ID/view atau /file/u/0/d/ID/view
            if (
                preg_match(
                    '#^/file/(?:u/\d+/)?d/([A-Za-z0-9_-]+)#',
                    $path,
                    $m
                )
            ) {
                return [
                    'tipe' => 'drive',
                    'id' => $m[1]
                ];
            }

            // Format /open?id=ID atau /uc?id=ID
            if ($path === '/open' || $path === '/uc') {
                parse_str($bagian['query'] ?? '', $q);

                if (
                    !empty($q['id']) &&
                    is_string($q['id']) &&
                    preg_match('/^[A-Za-z0-9_-]+$/', $q['id'])
                ) {
                    return [
                        'tipe' => 'drive',
                        'id' => $q['id']
                    ];
                }
            }

            return null;
        }

        if ($host === 'docs.google.com') {

            if (
                preg_match(
                    '#^/(document|presentation|spreadsheets)/d/([A-Za-z0-9_-]+)#',
                    $path,
                    $m
                )
            ) {
                return [
                    'tipe' => $m[1],
                    'id' => $m[2]
                ];
            }

            return null;
        }

        return null;
    }

    // Membuat URL pratinjau materi
    public function urlPratinjau($info)
    {
        if (!$info || empty($info['id']) || empty($info['tipe'])) {
            return null;
        }

        $id = $info['id'];

        switch ($info['tipe']) {

            case 'drive':
                return 'https://drive.google.com/file/d/' . $id . '/preview';

            case 'presentation':
                return 'https://docs.google.com/presentation/d/' . $id
                    . '/embed?start=false&loop=false';

            case 'document':
                return 'https://docs.google.com/document/d/' . $id . '/preview';

            case 'spreadsheets':
                return 'https://docs.google.com/spreadsheets/d/' . $id . '/preview';
        }

        return null;
    }

    // Membuat URL unduhan materi
    public function urlUnduh($info)
    {
        if (!$info || empty($info['id']) || empty($info['tipe'])) {
            return null;
        }

        $id = $info['id'];

        switch ($info['tipe']) {

            case 'drive':
                return 'https://drive.google.com/uc?export=download&id=' . $id;

            case 'presentation':
                return 'https://docs.google.com/presentation/d/' . $id . '/export/pptx';

            case 'document':
                return 'https://docs.google.com/document/d/' . $id . '/export/docx';

            case 'spreadsheets':
                return 'https://docs.google.com/spreadsheets/d/' . $id . '/export?format=xlsx';
        }

        return null;
    }
}