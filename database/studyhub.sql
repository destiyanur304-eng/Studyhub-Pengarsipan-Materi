-- ================================================================
-- STUDYHUB - DATABASE GABUNGAN
-- Gabungan dari studyhub.sql dan fitur_ulasan_forum.sql
-- Impor file ini ke database MySQL bernama `studyhub`.
-- ================================================================

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 27, 2026 at 06:21 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studyhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `keterangan` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `keterangan`) VALUES
(1, 'Teori', 'Materi kuliah teori'),
(2, 'Praktikum', 'Modul dan materi praktikum');

--
-- Triggers `kategori`
--
DELIMITER $$
CREATE TRIGGER `trg_kategori_log_del` AFTER DELETE ON `kategori` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'kategori', 'HAPUS', OLD.id,
            CONCAT('Menghapus kategori: ', OLD.nama_kategori));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_kategori_log_ins` AFTER INSERT ON `kategori` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'kategori', 'TAMBAH', NEW.id,
            CONCAT('Menambah kategori: ', NEW.nama_kategori));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_kategori_log_upd` AFTER UPDATE ON `kategori` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'kategori', 'UBAH', NEW.id,
            CONCAT('Mengubah kategori: ', NEW.nama_kategori));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int NOT NULL,
  `waktu` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pelaku_id` int DEFAULT NULL,
  `tabel` varchar(50) NOT NULL,
  `aksi` enum('TAMBAH','UBAH','HAPUS') NOT NULL,
  `data_id` int NOT NULL,
  `keterangan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `waktu`, `pelaku_id`, `tabel`, `aksi`, `data_id`, `keterangan`) VALUES
(3, '2026-11-24 11:42:21', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)'),
(4, '2026-11-24 11:42:25', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)'),
(5, '2026-11-24 11:43:34', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)'),
(6, '2026-11-24 11:45:04', 1, 'mata_kuliah', 'TAMBAH', 12, 'Menambah mata kuliah: apa saja'),
(7, '2026-11-24 11:46:17', 1, 'mata_kuliah', 'TAMBAH', 13, 'Menambah mata kuliah: iyaa'),
(8, '2026-11-24 11:46:44', 1, 'mata_kuliah', 'HAPUS', 12, 'Menghapus mata kuliah: apa saja'),
(9, '2026-11-24 11:46:48', 1, 'mata_kuliah', 'HAPUS', 13, 'Menghapus mata kuliah: iyaa'),
(10, '2026-11-24 11:48:44', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)'),
(11, '2026-11-24 11:48:55', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)'),
(12, '2026-11-24 12:01:04', 1, 'materi', 'UBAH', 2, 'Mengubah materi: common terms used in computer science'),
(13, '2026-11-24 12:01:11', 1, 'materi', 'UBAH', 2, 'Mengubah materi: common terms used in computer science'),
(14, '2026-11-24 12:10:07', 1, 'materi', 'UBAH', 2, 'Mengubah materi: common terms used in computer science'),
(15, '2026-11-24 12:25:57', 1, 'materi', 'UBAH', 2, 'Mengubah materi: common terms used in computer'),
(16, '2026-11-24 12:26:11', 1, 'materi', 'UBAH', 2, 'Mengubah materi: common terms used in computer science'),
(17, '2026-11-24 13:36:32', 1, 'materi', 'TAMBAH', 8, 'Menambah materi: Modul 2 - Mengenal Adobe Premiere'),
(18, '2026-11-24 21:04:23', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)'),
(19, '2026-11-24 21:05:42', 1, 'mahasiswa', 'UBAH', 1, 'Mengubah mahasiswa: destiya (2507071)');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nim` varchar(30) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `prodi_id` int NOT NULL,
  `semester` int NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id`, `nama`, `nim`, `username`, `password`, `prodi_id`, `semester`, `dibuat_pada`) VALUES
(1, 'destiya', '2507071', 'Destiya', '$2y$10$H.DX/YO2ee210HcwrtxN..A6Nl5WI5KUlyAmnInMN7Btb3KzyU8pO', 1, 3, '2026-11-23 13:35:02');

--
-- Triggers `mahasiswa`
--
DELIMITER $$
CREATE TRIGGER `trg_mhs_log_del` AFTER DELETE ON `mahasiswa` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'mahasiswa', 'HAPUS', OLD.id,
            CONCAT('Menghapus mahasiswa: ', OLD.nama, ' (', OLD.nim, ')'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mhs_log_ins` AFTER INSERT ON `mahasiswa` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'mahasiswa', 'TAMBAH', NEW.id,
            CONCAT('Menambah mahasiswa: ', NEW.nama, ' (', NEW.nim, ')'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mhs_log_upd` AFTER UPDATE ON `mahasiswa` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'mahasiswa', 'UBAH', NEW.id,
            CONCAT('Mengubah mahasiswa: ', NEW.nama, ' (', NEW.nim, ')'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mhs_semester_ins` BEFORE INSERT ON `mahasiswa` FOR EACH ROW BEGIN
    DECLARE maks INT;
    SELECT jumlah_semester INTO maks FROM program_studi WHERE id = NEW.prodi_id;
    IF NEW.semester < 1 OR NEW.semester > maks THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Semester mahasiswa melebihi jumlah semester prodi';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mhs_semester_upd` BEFORE UPDATE ON `mahasiswa` FOR EACH ROW BEGIN
    DECLARE maks INT;
    SELECT jumlah_semester INTO maks FROM program_studi WHERE id = NEW.prodi_id;
    IF NEW.semester < 1 OR NEW.semester > maks THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Semester mahasiswa melebihi jumlah semester prodi';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id` int NOT NULL,
  `nama_mk` varchar(150) NOT NULL,
  `prodi_id` int NOT NULL,
  `semester` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id`, `nama_mk`, `prodi_id`, `semester`) VALUES
(4, 'Animasi Dasar', 1, 3),
(5, 'Bahasa Inggris', 1, 3),
(6, 'Basis Data', 1, 3),
(7, 'Komunikasi Data dan Jaringan Komputer', 1, 3),
(8, 'Pemograman Berbasis Web', 1, 3),
(9, 'Pemograman Berorientasi Objek', 1, 3),
(10, 'Penyutingan Video', 1, 3),
(11, 'Rekayasa Kebutuhan Perangkat Lunak', 1, 3);

--
-- Triggers `mata_kuliah`
--
DELIMITER $$
CREATE TRIGGER `trg_mk_log_del` AFTER DELETE ON `mata_kuliah` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'mata_kuliah', 'HAPUS', OLD.id,
            CONCAT('Menghapus mata kuliah: ', OLD.nama_mk));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mk_log_ins` AFTER INSERT ON `mata_kuliah` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'mata_kuliah', 'TAMBAH', NEW.id,
            CONCAT('Menambah mata kuliah: ', NEW.nama_mk));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mk_log_upd` AFTER UPDATE ON `mata_kuliah` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'mata_kuliah', 'UBAH', NEW.id,
            CONCAT('Mengubah mata kuliah: ', NEW.nama_mk));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mk_semester_ins` BEFORE INSERT ON `mata_kuliah` FOR EACH ROW BEGIN
    DECLARE maks INT;
    SELECT jumlah_semester INTO maks FROM program_studi WHERE id = NEW.prodi_id;
    IF NEW.semester < 1 OR NEW.semester > maks THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Semester melebihi jumlah semester program studi';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_mk_semester_upd` BEFORE UPDATE ON `mata_kuliah` FOR EACH ROW BEGIN
    DECLARE maks INT;
    SELECT jumlah_semester INTO maks FROM program_studi WHERE id = NEW.prodi_id;
    IF NEW.semester < 1 OR NEW.semester > maks THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Semester melebihi jumlah semester program studi';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id` int NOT NULL,
  `judul` varchar(150) NOT NULL,
  `mata_kuliah_id` int NOT NULL,
  `tahun_arsip` year NOT NULL,
  `kategori_id` int NOT NULL,
  `jenis_berkas` enum('PDF','PPT') NOT NULL,
  `link_drive` text NOT NULL,
  `deskripsi` text,
  `jumlah_dilihat` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`id`, `judul`, `mata_kuliah_id`, `tahun_arsip`, `kategori_id`, `jenis_berkas`, `link_drive`, `deskripsi`, `jumlah_dilihat`) VALUES
(1, 'Modul 1', 4, '2026', 2, 'PDF', 'https://docs.google.com/document/d/1oRA6MMt-XtGJ5NRgVQpVdLRuxgwSZuaj/edit?usp=drive_link&ouid=102830842537984563694&rtpof=true&sd=true', '', 2),
(2, 'common terms used in computer science', 5, '2026', 1, 'PDF', 'https://drive.google.com/file/d/1W2b9xXrYerxWDONbAjA-uq5cf4a_Aw3Z/view?usp=drive_link', '', 2),
(3, 'Modul 1', 6, '2026', 2, 'PDF', 'https://drive.google.com/file/d/1LKW6xpT_-cPww05KGqTXf4qiXBNbFbb_/view?usp=drive_link', '', 2),
(5, 'Modul 1', 7, '2026', 2, 'PDF', 'https://drive.google.com/file/d/1VOPb304Kp6oCfojuARBi1EBXj8p_v7ss/view?usp=drive_link', '', 2),
(6, 'Kontak kuliah', 8, '2026', 2, 'PDF', 'https://drive.google.com/file/d/1Q5mXRM23paCAuzIBQbsaUGYGIVA1ebR_/view?usp=drive_link', '', 3),
(7, 'Modul 2', 9, '2026', 2, 'PDF', 'https://drive.google.com/file/d/1J2PGGTueoajN8bfGvTjhF_AhXmK9Nhld/view?usp=drive_link', '', 1),
(8, 'Modul 2 - Mengenal Adobe Premiere', 10, '2026', 2, 'PDF', 'https://drive.google.com/file/d/1qEkeNJJh9DSx0QMqY3HZLiI_6I7aEVpA/view?usp=drive_link', '', 2);

--
-- Triggers `materi`
--
DELIMITER $$
CREATE TRIGGER `trg_materi_log_del` AFTER DELETE ON `materi` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'materi', 'HAPUS', OLD.id,
            CONCAT('Menghapus materi: ', OLD.judul));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_materi_log_ins` AFTER INSERT ON `materi` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
    VALUES (@studyhub_user, 'materi', 'TAMBAH', NEW.id,
            CONCAT('Menambah materi: ', NEW.judul));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_materi_log_upd` AFTER UPDATE ON `materi` FOR EACH ROW BEGIN
    IF NEW.judul <> OLD.judul
       OR NEW.mata_kuliah_id <> OLD.mata_kuliah_id
       OR NEW.tahun_arsip <> OLD.tahun_arsip
       OR NEW.kategori_id <> OLD.kategori_id
       OR NEW.jenis_berkas <> OLD.jenis_berkas
       OR NEW.link_drive <> OLD.link_drive
       OR IFNULL(NEW.deskripsi, '') <> IFNULL(OLD.deskripsi, '') THEN
        INSERT INTO log_aktivitas (pelaku_id, tabel, aksi, data_id, keterangan)
        VALUES (@studyhub_user, 'materi', 'UBAH', NEW.id,
                CONCAT('Mengubah materi: ', NEW.judul));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id` int NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `jumlah_semester` int NOT NULL DEFAULT '6'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`id`, `nama_prodi`, `jumlah_semester`) VALUES
(1, 'D3 Manajemen Informatika', 6),
(2, 'S1 Ilmu Komputer', 8),
(3, 'S1 Sistem Informasi', 8);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_akses`
--

CREATE TABLE `riwayat_akses` (
  `id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `materi_id` int NOT NULL,
  `waktu_akses` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `riwayat_akses`
--

INSERT INTO `riwayat_akses` (`id`, `mahasiswa_id`, `materi_id`, `waktu_akses`) VALUES
(1, 1, 2, '2026-11-24 11:12:27'),
(2, 1, 6, '2026-11-24 11:16:23'),
(3, 1, 3, '2026-11-24 12:27:29'),
(4, 1, 3, '2026-11-24 12:28:47'),
(5, 1, 2, '2026-11-24 12:30:28'),
(6, 1, 1, '2026-11-24 12:30:35'),
(7, 1, 1, '2026-11-24 13:01:40'),
(8, 1, 5, '2026-11-24 13:13:17'),
(9, 1, 5, '2026-11-24 13:32:39'),
(10, 1, 8, '2026-11-24 13:38:25'),
(11, 1, 8, '2026-11-24 13:39:29'),
(12, 1, 7, '2026-11-24 13:40:03'),
(13, 1, 6, '2026-11-24 13:40:52'),
(14, 1, 6, '2026-11-24 20:59:11');

--
-- Triggers `riwayat_akses`
--
DELIMITER $$
CREATE TRIGGER `trg_akses_hitung` AFTER INSERT ON `riwayat_akses` FOR EACH ROW BEGIN
    UPDATE materi
    SET jumlah_dilihat = jumlah_dilihat + 1
    WHERE id = NEW.materi_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `dibuat_pada`) VALUES
(1, 'Administrator', 'admin', '$2y$10$9Bm6gALQGLnxBUkD1xUbNO3x7aInRYVeyY3GHCTNiJkION1xYDdh.', '2026-11-23 13:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `users_lama`
--

CREATE TABLE `users_lama` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nim` varchar(30) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa') NOT NULL,
  `prodi_id` int DEFAULT NULL,
  `semester` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users_lama`
--

INSERT INTO `users_lama` (`id`, `nama`, `nim`, `username`, `password`, `role`, `prodi_id`, `semester`) VALUES
(1, 'Administrator', NULL, 'admin', '$2y$10$9Bm6gALQGLnxBUkD1xUbNO3x7aInRYVeyY3GHCTNiJkION1xYDdh.', 'admin', NULL, NULL),
(3, 'destiya', '2507071', 'Destiya', '$2y$10$H.DX/YO2ee210HcwrtxN..A6Nl5WI5KUlyAmnInMN7Btb3KzyU8pO', 'mahasiswa', 1, 3);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_materi_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `v_materi_lengkap` (
`id` int
,`judul` varchar(150)
,`tahun_arsip` year
,`nama_kategori` varchar(50)
,`jenis_berkas` enum('PDF','PPT')
,`jumlah_dilihat` int
,`nama_mk` varchar(150)
,`semester` int
,`nama_prodi` varchar(100)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_waktu` (`waktu`),
  ADD KEY `idx_log_tabel_aksi` (`tabel`,`aksi`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_mhs_prodi_semester` (`prodi_id`,`semester`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mk_prodi_semester` (`nama_mk`,`prodi_id`,`semester`),
  ADD KEY `idx_mk_prodi_semester` (`prodi_id`,`semester`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_materi_kategori` (`kategori_id`),
  ADD KEY `idx_materi_tahun` (`tahun_arsip`),
  ADD KEY `idx_materi_mk_tahun` (`mata_kuliah_id`,`tahun_arsip`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_akses`
--
ALTER TABLE `riwayat_akses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_akses_materi` (`materi_id`),
  ADD KEY `idx_akses_mhs_waktu` (`mahasiswa_id`,`waktu_akses`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users_lama`
--
ALTER TABLE `users_lama`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `prodi_id` (`prodi_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `riwayat_akses`
--
ALTER TABLE `riwayat_akses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users_lama`
--
ALTER TABLE `users_lama`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------

--
-- Structure for view `v_materi_lengkap`
--
DROP TABLE IF EXISTS `v_materi_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_materi_lengkap`  AS SELECT `materi`.`id` AS `id`, `materi`.`judul` AS `judul`, `materi`.`tahun_arsip` AS `tahun_arsip`, `kategori`.`nama_kategori` AS `nama_kategori`, `materi`.`jenis_berkas` AS `jenis_berkas`, `materi`.`jumlah_dilihat` AS `jumlah_dilihat`, `mata_kuliah`.`nama_mk` AS `nama_mk`, `mata_kuliah`.`semester` AS `semester`, `program_studi`.`nama_prodi` AS `nama_prodi` FROM (((`materi` join `kategori` on((`materi`.`kategori_id` = `kategori`.`id`))) join `mata_kuliah` on((`materi`.`mata_kuliah_id` = `mata_kuliah`.`id`))) join `program_studi` on((`mata_kuliah`.`prodi_id` = `program_studi`.`id`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `fk_mahasiswa_prodi` FOREIGN KEY (`prodi_id`) REFERENCES `program_studi` (`id`);

--
-- Constraints for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `program_studi` (`id`);

--
-- Constraints for table `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `fk_materi_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`),
  ADD CONSTRAINT `materi_ibfk_1` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_akses`
--
ALTER TABLE `riwayat_akses`
  ADD CONSTRAINT `fk_akses_mahasiswa` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_akses_materi` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_lama`
--
ALTER TABLE `users_lama`
  ADD CONSTRAINT `users_lama_ibfk_1` FOREIGN KEY (`prodi_id`) REFERENCES `program_studi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ================================================================
-- TAMBAHAN FITUR: RATING, ULASAN, DAN FORUM DISKUSI
-- ================================================================

-- =====================================================================
-- StudyHub - Migrasi fitur Rating & Ulasan + Forum Diskusi
-- Jalankan SEKALI pada database `studyhub` (phpMyAdmin > tab SQL).
-- Mengikuti gaya penamaan di studyhub.sql:
--   * kolom Indonesia (mahasiswa_id, dibuat_pada, ...)
--   * engine InnoDB, charset utf8mb4, collation utf8mb4_0900_ai_ci
-- Jika memakai MariaDB (bukan MySQL 8), ganti collation menjadi
-- utf8mb4_general_ci.
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- 1. Rating & ulasan materi
--    Satu mahasiswa hanya boleh punya satu ulasan per materi
--    (dapat diubah kembali), dijaga oleh UNIQUE KEY uq_ulasan.
-- ---------------------------------------------------------------------
CREATE TABLE `ulasan_materi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `materi_id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `komentar` text,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ulasan` (`materi_id`,`mahasiswa_id`),
  KEY `idx_ulasan_mahasiswa` (`mahasiswa_id`),
  CONSTRAINT `chk_ulasan_rating` CHECK (`rating` BETWEEN 1 AND 5),
  CONSTRAINT `fk_ulasan_materi` FOREIGN KEY (`materi_id`)
    REFERENCES `materi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ulasan_mahasiswa` FOREIGN KEY (`mahasiswa_id`)
    REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- 2. Forum: postingan (thread)
--    mata_kuliah_id opsional; bila mata kuliah dihapus admin,
--    postingan tetap ada (SET NULL).
-- ---------------------------------------------------------------------
CREATE TABLE `forum_postingan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` int NOT NULL,
  `mata_kuliah_id` int DEFAULT NULL,
  `kategori` enum('tanya_jawab','request_materi','review_materi') NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `status` enum('terbuka','selesai') NOT NULL DEFAULT 'terbuka',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_forum_mahasiswa` (`mahasiswa_id`),
  KEY `idx_forum_mk` (`mata_kuliah_id`),
  KEY `idx_forum_kategori_status` (`kategori`,`status`),
  KEY `idx_forum_dibuat` (`dibuat_pada`),
  CONSTRAINT `fk_forum_mahasiswa` FOREIGN KEY (`mahasiswa_id`)
    REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_forum_mk` FOREIGN KEY (`mata_kuliah_id`)
    REFERENCES `mata_kuliah` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- 3. Forum: balasan / tanggapan pada postingan
-- ---------------------------------------------------------------------
CREATE TABLE `forum_balasan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `postingan_id` int NOT NULL,
  `mahasiswa_id` int NOT NULL,
  `isi` text NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_balasan_postingan` (`postingan_id`,`dibuat_pada`),
  KEY `idx_balasan_mahasiswa` (`mahasiswa_id`),
  CONSTRAINT `fk_balasan_postingan` FOREIGN KEY (`postingan_id`)
    REFERENCES `forum_postingan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_balasan_mahasiswa` FOREIGN KEY (`mahasiswa_id`)
    REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

COMMIT;
