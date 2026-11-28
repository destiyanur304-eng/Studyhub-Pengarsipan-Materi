<?php

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Mengambil seluruh data mahasiswa
    public function getAllMahasiswa()
    {
        $query = $this->pdo->prepare(
            "SELECT
                mahasiswa.*,
                program_studi.nama_prodi,
                program_studi.jumlah_semester
             FROM mahasiswa
             JOIN program_studi
                ON mahasiswa.prodi_id = program_studi.id
             ORDER BY mahasiswa.nama ASC"
        );

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil mahasiswa berdasarkan ID
    public function getById($id)
    {
        $query = $this->pdo->prepare(
            "SELECT *
             FROM mahasiswa
             WHERE id = ?
             LIMIT 1"
        );

        $query->execute([(int) $id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    // Mengambil seluruh program studi
    public function getProgramStudi()
    {
        $query = $this->pdo->prepare(
            "SELECT id, nama_prodi, jumlah_semester
             FROM program_studi
             ORDER BY nama_prodi ASC"
        );

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil batas semester berdasarkan program studi
    public function getJumlahSemesterProdi($prodiId)
    {
        $query = $this->pdo->prepare(
            "SELECT jumlah_semester
             FROM program_studi
             WHERE id = ?
             LIMIT 1"
        );

        $query->execute([(int) $prodiId]);

        return $query->fetchColumn();
    }

    // Menambahkan mahasiswa
    public function create($data)
    {
        $password = password_hash(
            $data['password'],
            PASSWORD_DEFAULT
        );

        $query = $this->pdo->prepare(
            "INSERT INTO mahasiswa
                (nama, nim, username, password, prodi_id, semester)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        return $query->execute([
            trim($data['nama']),
            trim($data['nim']),
            trim($data['username']),
            $password,
            (int) $data['prodi_id'],
            (int) $data['semester']
        ]);
    }

    // Mengubah data mahasiswa
    public function update($id, $data)
    {
        if (trim($data['password'] ?? '') !== '') {

            $password = password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            );

            $query = $this->pdo->prepare(
                "UPDATE mahasiswa
                 SET nama = ?,
                     nim = ?,
                     username = ?,
                     password = ?,
                     prodi_id = ?,
                     semester = ?
                 WHERE id = ?"
            );

            return $query->execute([
                trim($data['nama']),
                trim($data['nim']),
                trim($data['username']),
                $password,
                (int) $data['prodi_id'],
                (int) $data['semester'],
                (int) $id
            ]);
        }

        $query = $this->pdo->prepare(
            "UPDATE mahasiswa
             SET nama = ?,
                 nim = ?,
                 username = ?,
                 prodi_id = ?,
                 semester = ?
             WHERE id = ?"
        );

        return $query->execute([
            trim($data['nama']),
            trim($data['nim']),
            trim($data['username']),
            (int) $data['prodi_id'],
            (int) $data['semester'],
            (int) $id
        ]);
    }

    // Menghapus mahasiswa
    public function delete($id)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM mahasiswa WHERE id = ?"
        );

        return $query->execute([(int) $id]);
    }
}