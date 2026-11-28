<?php

class MataKuliah
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll()
    {
        $query = $this->pdo->prepare(
            "SELECT
                mata_kuliah.*,
                program_studi.nama_prodi,
                program_studi.jumlah_semester
             FROM mata_kuliah
             JOIN program_studi
                ON mata_kuliah.prodi_id = program_studi.id
             ORDER BY program_studi.nama_prodi ASC,
                      mata_kuliah.semester ASC,
                      mata_kuliah.nama_mk ASC"
        );

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = $this->pdo->prepare(
            "SELECT * FROM mata_kuliah WHERE id = ?"
        );

        $query->execute([$id]);

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $query = $this->pdo->prepare(
            "INSERT INTO mata_kuliah
                (nama_mk, prodi_id, semester)
             VALUES (?, ?, ?)"
        );

        return $query->execute([
            trim($data['nama_mk']),
            (int) $data['prodi_id'],
            (int) $data['semester']
        ]);
    }

    public function update($id, $data)
    {
        $query = $this->pdo->prepare(
            "UPDATE mata_kuliah
             SET nama_mk = ?,
                 prodi_id = ?,
                 semester = ?
             WHERE id = ?"
        );

        return $query->execute([
            trim($data['nama_mk']),
            (int) $data['prodi_id'],
            (int) $data['semester'],
            (int) $id
        ]);
    }

    public function delete($id)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM mata_kuliah WHERE id = ?"
        );

        return $query->execute([(int) $id]);
    }

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
}