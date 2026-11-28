<?php

class Materi
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Mengambil seluruh materi
    public function getAll()
    {
        $sql = "SELECT m.*, mk.nama_mk, mk.prodi_id,
                       mk.semester, ps.nama_prodi,
                       k.nama_kategori AS kategori
                FROM materi m
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id
                JOIN program_studi ps ON mk.prodi_id = ps.id
                JOIN kategori k ON m.kategori_id = k.id
                ORDER BY m.tahun_arsip DESC, m.judul ASC";

        return $this->pdo->query($sql)->fetchAll();
    }

    // Mengambil materi berdasarkan ID
    public function getById($id)
    {
        $sql = "SELECT m.*, mk.nama_mk, mk.prodi_id,
                       mk.semester, ps.nama_prodi,
                       k.nama_kategori AS kategori
                FROM materi m
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id
                JOIN program_studi ps ON mk.prodi_id = ps.id
                JOIN kategori k ON m.kategori_id = k.id
                WHERE m.id = ? LIMIT 1";

        $query = $this->pdo->prepare($sql);
        $query->execute([$id]);

        return $query->fetch();
    }

    // Mengambil daftar kategori beserta ID
    public function getKategori()
    {
        return $this->pdo->query(
            "SELECT id, nama_kategori
             FROM kategori
             ORDER BY nama_kategori ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil daftar nama kategori
    public function getNamaKategori()
    {
        return array_column($this->getKategori(), 'nama_kategori');
    }

    // Mengecek apakah kategori tersedia
    public function kategoriAda($kategoriId)
    {
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) FROM kategori WHERE id = ?"
        );

        $query->execute([$kategoriId]);

        return (int) $query->fetchColumn() > 0;
    }

    // Menambahkan materi
    public function create($data)
    {
        $sql = "INSERT INTO materi
                (judul, mata_kuliah_id, tahun_arsip, kategori_id,
                 jenis_berkas, link_drive, deskripsi)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $query = $this->pdo->prepare($sql);

        return $query->execute([
            $data['judul'],
            $data['mata_kuliah_id'],
            $data['tahun_arsip'],
            $data['kategori_id'],
            $data['jenis_berkas'],
            $data['link_drive'],
            $data['deskripsi'] ?? ''
        ]);
    }

    // Mengubah materi
    public function update($id, $data)
    {
        $sql = "UPDATE materi
                SET judul = ?,
                    mata_kuliah_id = ?,
                    tahun_arsip = ?,
                    kategori_id = ?,
                    jenis_berkas = ?,
                    link_drive = ?,
                    deskripsi = ?
                WHERE id = ?";

        $query = $this->pdo->prepare($sql);

        return $query->execute([
            $data['judul'],
            $data['mata_kuliah_id'],
            $data['tahun_arsip'],
            $data['kategori_id'],
            $data['jenis_berkas'],
            $data['link_drive'],
            $data['deskripsi'] ?? '',
            $id
        ]);
    }

    // Menghapus materi
    public function delete($id)
    {
        $query = $this->pdo->prepare(
            "DELETE FROM materi WHERE id = ?"
        );

        return $query->execute([$id]);
    }

    // Mengambil mata kuliah
    public function getMataKuliah()
    {
        $sql = "SELECT mk.*, ps.nama_prodi
                FROM mata_kuliah mk
                JOIN program_studi ps ON mk.prodi_id = ps.id
                ORDER BY ps.nama_prodi, mk.semester, mk.nama_mk";

        return $this->pdo->query($sql)->fetchAll();
    }

    // Mengambil program studi
    public function getProgramStudi()
    {
        return $this->pdo->query(
            "SELECT * FROM program_studi ORDER BY nama_prodi ASC"
        )->fetchAll();
    }

    // Mengecek judul materi yang sudah ada
    public function judulAda($mkId, $tahun, $kategori, $judul)
    {
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) FROM materi
             WHERE mata_kuliah_id = ?
               AND tahun_arsip = ?
               AND kategori_id = ?
               AND LOWER(judul) = LOWER(?)"
        );

        $query->execute([
            $mkId,
            $tahun,
            $kategori,
            $judul
        ]);

        return (int) $query->fetchColumn() > 0;
    }

    // Pencarian dan filter Admin
    public function search(
        $keyword = '',
        $tahun = '',
        $prodi = '',
        $semester = '',
        $kategori = ''
    ) {
        $sql = "SELECT m.*, mk.nama_mk, mk.prodi_id,
                       mk.semester, ps.nama_prodi,
                       k.nama_kategori AS kategori
                FROM materi m
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id
                JOIN program_studi ps ON mk.prodi_id = ps.id
                JOIN kategori k ON m.kategori_id = k.id
                WHERE 1=1";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (m.judul LIKE ? OR mk.nama_mk LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        if ($tahun !== '') {
            $sql .= " AND m.tahun_arsip = ?";
            $params[] = $tahun;
        }

        if ($prodi !== '') {
            $sql .= " AND mk.prodi_id = ?";
            $params[] = $prodi;
        }

        if ($semester !== '') {
            $sql .= " AND mk.semester = ?";
            $params[] = $semester;
        }

        if ($kategori !== '') {
            $sql .= " AND k.nama_kategori = ?";
            $params[] = $kategori;
        }

        $sql .= " ORDER BY m.tahun_arsip DESC, m.judul ASC";

        $query = $this->pdo->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    // Jumlah semester berdasarkan program studi
    public function getJumlahSemester($prodiId)
    {
        $query = $this->pdo->prepare(
            "SELECT jumlah_semester
             FROM program_studi
             WHERE id = ?"
        );

        $query->execute([$prodiId]);

        $jumlah = $query->fetchColumn();

        return $jumlah === false ? 0 : (int) $jumlah;
    }

    // Ringkasan materi berdasarkan tahun
    public function getRingkasanTahun($prodiId)
    {
        $sql = "SELECT m.tahun_arsip,
                       COUNT(m.id) AS jumlah_materi
                FROM materi m
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id
                WHERE mk.prodi_id = ?
                GROUP BY m.tahun_arsip
                ORDER BY m.tahun_arsip DESC";

        $query = $this->pdo->prepare($sql);
        $query->execute([$prodiId]);

        $hasil = [];

        foreach ($query->fetchAll() as $baris) {
            $hasil[(string) $baris['tahun_arsip']] = $baris;
        }

        return $hasil;
    }

    // Ringkasan materi berdasarkan semester dan tahun
    public function getRingkasanSemesterTahun($prodiId, $tahun)
    {
        $sql = "SELECT mk.semester,
                       COUNT(DISTINCT mk.id) AS jumlah_mk,
                       COUNT(m.id) AS jumlah_materi
                FROM materi m
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id
                WHERE mk.prodi_id = ?
                  AND m.tahun_arsip = ?
                GROUP BY mk.semester
                ORDER BY mk.semester ASC";

        $query = $this->pdo->prepare($sql);
        $query->execute([$prodiId, $tahun]);

        $hasil = [];

        foreach ($query->fetchAll() as $baris) {
            $hasil[(int) $baris['semester']] = $baris;
        }

        return $hasil;
    }

    // Daftar mata kuliah pada tahun dan semester tertentu
    public function getMataKuliahTahunSemester(
        $prodiId,
        $tahun,
        $semester
    ) {
        $sql = "SELECT mk.id, mk.nama_mk,
                       COUNT(m.id) AS jumlah_materi
                FROM mata_kuliah mk
                JOIN materi m ON m.mata_kuliah_id = mk.id
                WHERE mk.prodi_id = ?
                  AND mk.semester = ?
                  AND m.tahun_arsip = ?
                GROUP BY mk.id, mk.nama_mk
                ORDER BY mk.nama_mk ASC";

        $query = $this->pdo->prepare($sql);
        $query->execute([$prodiId, $semester, $tahun]);

        return $query->fetchAll();
    }

    // Validasi kepemilikan mata kuliah
    public function getMataKuliahValid($mkId, $prodiId, $semester)
    {
        $query = $this->pdo->prepare(
            "SELECT * FROM mata_kuliah
             WHERE id = ? AND prodi_id = ? AND semester = ?"
        );

        $query->execute([$mkId, $prodiId, $semester]);

        return $query->fetch();
    }

    // Mengambil materi di dalam folder mata kuliah
    public function getBerkasMataKuliah($mkId, $tahun, $filters)
    {
        $sql = "SELECT m.*, k.nama_kategori AS kategori,
                       ur.rata_rating, ur.jumlah_ulasan
                FROM materi m
                JOIN kategori k ON m.kategori_id = k.id
                LEFT JOIN (
                    SELECT materi_id,
                           AVG(rating) AS rata_rating,
                           COUNT(*) AS jumlah_ulasan
                    FROM ulasan_materi
                    GROUP BY materi_id
                ) ur ON ur.materi_id = m.id
                WHERE m.mata_kuliah_id = ?
                  AND m.tahun_arsip = ?";

        $params = [$mkId, $tahun];

        if (!empty($filters['keyword'])) {
            $sql .= " AND m.judul LIKE ?";
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND k.nama_kategori = ?";
            $params[] = $filters['kategori'];
        }

        $sql .= " ORDER BY k.nama_kategori ASC, m.judul ASC";

        $query = $this->pdo->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }

    // Pencarian materi mahasiswa
    public function cariMahasiswa($prodiId, $filters)
    {
        $sql = "SELECT m.*, mk.nama_mk, mk.semester,
                       k.nama_kategori AS kategori,
                       ur.rata_rating, ur.jumlah_ulasan
                FROM materi m
                JOIN mata_kuliah mk ON m.mata_kuliah_id = mk.id
                JOIN kategori k ON m.kategori_id = k.id
                LEFT JOIN (
                    SELECT materi_id,
                           AVG(rating) AS rata_rating,
                           COUNT(*) AS jumlah_ulasan
                    FROM ulasan_materi
                    GROUP BY materi_id
                ) ur ON ur.materi_id = m.id
                WHERE mk.prodi_id = ?";

        $params = [$prodiId];

        if (!empty($filters['keyword'])) {
            $sql .= " AND (m.judul LIKE ? OR mk.nama_mk LIKE ?)";
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['tahun'])) {
            $sql .= " AND m.tahun_arsip = ?";
            $params[] = $filters['tahun'];
        }

        if (!empty($filters['semester'])) {
            $sql .= " AND mk.semester = ?";
            $params[] = $filters['semester'];
        }

        if (!empty($filters['kategori'])) {
            $sql .= " AND k.nama_kategori = ?";
            $params[] = $filters['kategori'];
        }

        $sql .= " ORDER BY m.tahun_arsip DESC,
                           mk.semester ASC,
                           mk.nama_mk ASC,
                           m.judul ASC";

        $query = $this->pdo->prepare($sql);
        $query->execute($params);

        return $query->fetchAll();
    }
}