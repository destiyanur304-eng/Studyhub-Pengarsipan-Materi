<?php

class Ulasan
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Jumlah ulasan dan rata-rata rating sebuah materi
    public function getRingkasan($materiId)
    {
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) AS jumlah,
                    AVG(rating) AS rata
             FROM ulasan_materi
             WHERE materi_id = ?"
        );

        $query->execute([(int) $materiId]);

        $baris = $query->fetch();

        return [
            'jumlah' => (int) ($baris['jumlah'] ?? 0),
            'rata'   => $baris['rata'] !== null ? (float) $baris['rata'] : 0.0
        ];
    }

    // Sebaran jumlah ulasan per bintang (5 sampai 1)
    public function getDistribusi($materiId)
    {
        $query = $this->pdo->prepare(
            "SELECT rating, COUNT(*) AS jumlah
             FROM ulasan_materi
             WHERE materi_id = ?
             GROUP BY rating"
        );

        $query->execute([(int) $materiId]);

        $hasil = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        foreach ($query->fetchAll() as $baris) {
            $hasil[(int) $baris['rating']] = (int) $baris['jumlah'];
        }

        return $hasil;
    }

    // Ulasan milik satu mahasiswa pada satu materi
    public function getByMahasiswa($materiId, $mahasiswaId)
    {
        $query = $this->pdo->prepare(
            "SELECT * FROM ulasan_materi
             WHERE materi_id = ? AND mahasiswa_id = ?
             LIMIT 1"
        );

        $query->execute([(int) $materiId, (int) $mahasiswaId]);

        return $query->fetch();
    }

    // Ulasan dari mahasiswa lain (tidak termasuk milik sendiri)
    public function getDaftarLain($materiId, $mahasiswaId)
    {
        $query = $this->pdo->prepare(
            "SELECT u.*, mh.nama, mh.semester
             FROM ulasan_materi u
             JOIN mahasiswa mh ON u.mahasiswa_id = mh.id
             WHERE u.materi_id = ?
               AND u.mahasiswa_id <> ?
             ORDER BY u.diperbarui_pada DESC"
        );

        $query->execute([(int) $materiId, (int) $mahasiswaId]);

        return $query->fetchAll();
    }

    // Menyimpan ulasan baru atau memperbarui ulasan yang sudah ada
    public function simpan($materiId, $mahasiswaId, $rating, $komentar)
    {
        $query = $this->pdo->prepare(
            "INSERT INTO ulasan_materi
                (materi_id, mahasiswa_id, rating, komentar)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                komentar = VALUES(komentar)"
        );

        return $query->execute([
            (int) $materiId,
            (int) $mahasiswaId,
            (int) $rating,
            $komentar
        ]);
    }

    // Menghapus ulasan milik sendiri
    public function hapus($materiId, $mahasiswaId)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM ulasan_materi
             WHERE materi_id = ? AND mahasiswa_id = ?"
        );

        return $query->execute([(int) $materiId, (int) $mahasiswaId]);
    }
}
