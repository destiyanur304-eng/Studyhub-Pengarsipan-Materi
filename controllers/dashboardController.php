<?php

class DashboardController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Statistik untuk dashboard admin
    public function getAdminStats()
    {
        $mahasiswa = $this->pdo->query(
            "SELECT COUNT(*) FROM mahasiswa"
        )->fetchColumn();

        $matakuliah = $this->pdo->query(
            "SELECT COUNT(*) FROM mata_kuliah"
        )->fetchColumn();

        $materi = $this->pdo->query(
            "SELECT COUNT(*) FROM materi"
        )->fetchColumn();

        return [
            'mahasiswa' => $mahasiswa,
            'matakuliah' => $matakuliah,
            'materi' => $materi
        ];
    }

    // Statistik dashboard mahasiswa
    public function getMahasiswaStats($prodiId, $semester)
    {
        $queryMK = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM mata_kuliah
             WHERE prodi_id = ?
             AND semester <= ?"
        );

        $queryMK->execute([$prodiId, $semester]);

        $queryMateri = $this->pdo->prepare(
            "SELECT COUNT(*)
             FROM materi
             JOIN mata_kuliah
                ON materi.mata_kuliah_id = mata_kuliah.id
             WHERE mata_kuliah.prodi_id = ?
             AND mata_kuliah.semester <= ?"
        );

        $queryMateri->execute([$prodiId, $semester]);

        return [
            'matakuliah' => $queryMK->fetchColumn(),
            'materi' => $queryMateri->fetchColumn()
        ];
    }

    // Mengambil mata kuliah terpopuler sesuai prodi dan semester
    public function getMataKuliahTerpopuler($prodiId, $semester, $limit = 5)
    {
        $limit = max(1, min((int) $limit, 10));

        $query = $this->pdo->prepare(
            "SELECT
                mata_kuliah.id,
                mata_kuliah.nama_mk,
                COUNT(riwayat_akses.id) AS jumlah_akses
             FROM mata_kuliah
             LEFT JOIN materi
                ON materi.mata_kuliah_id = mata_kuliah.id
             LEFT JOIN riwayat_akses
                ON riwayat_akses.materi_id = materi.id
             WHERE mata_kuliah.prodi_id = ?
               AND mata_kuliah.semester = ?
             GROUP BY
                mata_kuliah.id,
                mata_kuliah.nama_mk
             ORDER BY
                jumlah_akses DESC,
                mata_kuliah.nama_mk ASC
             LIMIT $limit"
        );

        $query->execute([
            $prodiId,
            $semester
        ]);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}