<?php

class Forum
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Bagian SELECT yang dipakai bersama oleh daftar dan detail postingan
    private function selectDasar()
    {
        return "SELECT p.*,
                       mh.nama AS nama_penulis,
                       mh.prodi_id AS prodi_penulis,
                       mk.nama_mk,
                       COALESCE(b.jumlah_balasan, 0) AS jumlah_balasan
                FROM forum_postingan p
                JOIN mahasiswa mh ON p.mahasiswa_id = mh.id
                LEFT JOIN mata_kuliah mk ON p.mata_kuliah_id = mk.id
                LEFT JOIN (
                    SELECT postingan_id, COUNT(*) AS jumlah_balasan
                    FROM forum_balasan
                    GROUP BY postingan_id
                ) b ON b.postingan_id = p.id";
    }

    // Daftar postingan pada program studi tertentu
    public function getDaftar($prodiId, $filters = [])
    {
        $sql = $this->selectDasar() . " WHERE mh.prodi_id = ?";

        $params = [(int) $prodiId];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (p.judul LIKE ? OR p.isi LIKE ?)";
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND p.kategori = ?";
            $params[] = $filters['kategori'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['mata_kuliah_id'])) {
            $sql .= " AND p.mata_kuliah_id = ?";
            $params[] = (int) $filters['mata_kuliah_id'];
        }

        $sql .= " ORDER BY p.dibuat_pada DESC, p.id DESC LIMIT 200";

        $query = $this->pdo->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    // Detail satu postingan
    public function getById($id)
    {
        $query = $this->pdo->prepare(
            $this->selectDasar() . " WHERE p.id = ? LIMIT 1"
        );

        $query->execute([(int) $id]);

        return $query->fetch();
    }

    // Membuat postingan baru, mengembalikan ID postingan
    public function create($data)
    {
        $query = $this->pdo->prepare(
            "INSERT INTO forum_postingan
                (mahasiswa_id, mata_kuliah_id, kategori, judul, isi)
             VALUES (?, ?, ?, ?, ?)"
        );

        $query->execute([
            (int) $data['mahasiswa_id'],
            $data['mata_kuliah_id'] !== null
                ? (int) $data['mata_kuliah_id']
                : null,
            $data['kategori'],
            $data['judul'],
            $data['isi']
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // Mengubah status (hanya untuk pemilik postingan)
    public function setStatus($id, $mahasiswaId, $status)
    {
        $query = $this->pdo->prepare(
            "UPDATE forum_postingan
             SET status = ?
             WHERE id = ? AND mahasiswa_id = ?"
        );

        return $query->execute([$status, (int) $id, (int) $mahasiswaId]);
    }

    // Menghapus postingan (hanya untuk pemilik postingan)
    public function hapusPostingan($id, $mahasiswaId)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM forum_postingan
             WHERE id = ? AND mahasiswa_id = ?"
        );

        return $query->execute([(int) $id, (int) $mahasiswaId]);
    }

    // Seluruh balasan pada satu postingan (urut dari terlama)
    public function getBalasan($postinganId)
    {
        $query = $this->pdo->prepare(
            "SELECT b.*, mh.nama, mh.semester
             FROM forum_balasan b
             JOIN mahasiswa mh ON b.mahasiswa_id = mh.id
             WHERE b.postingan_id = ?
             ORDER BY b.dibuat_pada ASC, b.id ASC"
        );

        $query->execute([(int) $postinganId]);

        return $query->fetchAll();
    }

    // Menambahkan balasan
    public function tambahBalasan($postinganId, $mahasiswaId, $isi)
    {
        $query = $this->pdo->prepare(
            "INSERT INTO forum_balasan
                (postingan_id, mahasiswa_id, isi)
             VALUES (?, ?, ?)"
        );

        return $query->execute([
            (int) $postinganId,
            (int) $mahasiswaId,
            $isi
        ]);
    }

    // Menghapus balasan milik sendiri pada postingan tertentu
    public function hapusBalasan($id, $postinganId, $mahasiswaId)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM forum_balasan
             WHERE id = ? AND postingan_id = ? AND mahasiswa_id = ?"
        );

        return $query->execute([
            (int) $id,
            (int) $postinganId,
            (int) $mahasiswaId
        ]);
    }

    // Mata kuliah milik program studi (untuk pilihan tautan postingan)
    public function getMataKuliahProdi($prodiId)
    {
        $query = $this->pdo->prepare(
            "SELECT id, nama_mk, semester
             FROM mata_kuliah
             WHERE prodi_id = ?
             ORDER BY semester ASC, nama_mk ASC"
        );

        $query->execute([(int) $prodiId]);

        return $query->fetchAll();
    }

    // Memastikan mata kuliah milik program studi tersebut
    public function mataKuliahValid($mkId, $prodiId)
    {
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) FROM mata_kuliah
             WHERE id = ? AND prodi_id = ?"
        );

        $query->execute([(int) $mkId, (int) $prodiId]);

        return (int) $query->fetchColumn() > 0;
    }
}
