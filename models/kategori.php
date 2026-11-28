<?php

class Kategori
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Mengambil seluruh data kategori
    public function getAll()
    {
        $sql = "SELECT id, nama_kategori, keterangan
                FROM kategori
                ORDER BY id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil kategori berdasarkan ID
    public function getById($id)
    {
        $sql = "SELECT id, nama_kategori, keterangan
                FROM kategori
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Memeriksa apakah nama kategori sudah digunakan
    public function namaSudahAda($nama, $kecualiId = null)
    {
        $sql = "SELECT COUNT(*)
                FROM kategori
                WHERE LOWER(nama_kategori) = LOWER(:nama)";

        $params = ['nama' => $nama];

        if ($kecualiId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $kecualiId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    // Menambahkan kategori
    public function create($data)
    {
        $sql = "INSERT INTO kategori (nama_kategori, keterangan)
                VALUES (:nama_kategori, :keterangan)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'nama_kategori' => $data['nama_kategori'],
            'keterangan' => $data['keterangan']
        ]);
    }

    // Mengubah kategori
    public function update($id, $data)
    {
        $sql = "UPDATE kategori
                SET nama_kategori = :nama_kategori,
                    keterangan = :keterangan
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'nama_kategori' => $data['nama_kategori'],
            'keterangan' => $data['keterangan'],
            'id' => $id
        ]);
    }

    // Menghapus kategori
    public function delete($id)
    {
        $sql = "DELETE FROM kategori WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}