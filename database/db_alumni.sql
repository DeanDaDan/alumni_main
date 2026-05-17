-- --------------------------------------------------------
-- Database: db_alumni
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `db_alumni` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_alumni`;

-- --------------------------------------------------------
-- Struktur tabel `alumni`
-- --------------------------------------------------------

CREATE TABLE `alumni` (
  `id_alumni`   INT(11)      NOT NULL AUTO_INCREMENT,
  `nim`         VARCHAR(20)  NOT NULL,
  `nama`        VARCHAR(100) NOT NULL,
  `angkatan`    YEAR         NOT NULL,
  `jurusan`     VARCHAR(100) NOT NULL,
  `email`       VARCHAR(100) NOT NULL,
  `no_hp`       VARCHAR(20)  NOT NULL,
  `pekerjaan`   VARCHAR(100) DEFAULT NULL,
  `perusahaan`  VARCHAR(150) DEFAULT NULL,
  `alamat`      TEXT         DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alumni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Struktur tabel `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `user_id`  INT(11)     NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role`     ENUM('user','admin','superadmin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data awal tabel `users`
-- --------------------------------------------------------

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'user',       'user',       'user'),
(2, 'admin',      'admin',      'admin'),
(3, 'superadmin', 'superadmin', 'superadmin');

-- --------------------------------------------------------
-- Contoh data alumni
-- --------------------------------------------------------

INSERT INTO `alumni` (`nim`, `nama`, `angkatan`, `jurusan`, `email`, `no_hp`, `pekerjaan`, `perusahaan`, `alamat`) VALUES
('553241167', 'Syamil Cholid Atsani',      2024, 'Rekayasa Perangkat Lunak', 'syamil@email.com',      '0812345678910', 'Software Engineer',  'PT Telkom Indonesia', 'Pringsewu, Lampung');
