-- Apotek Alifa Database Schema
-- Generated for portfolio use
-- Import this file before running the application

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Database
-- ----------------------------
CREATE DATABASE IF NOT EXISTS `apotek_alifa`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `apotek_alifa`;

-- ----------------------------
-- Table: kategori
-- ----------------------------
CREATE TABLE IF NOT EXISTS `kategori` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: pengguna
-- ----------------------------
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `nama_pengguna` VARCHAR(50) NOT NULL,
  `surel` VARCHAR(100) NOT NULL,
  `sandi` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(100) NOT NULL DEFAULT 'user-1.png',
  `peran` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `waktu_dibuat` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama_pengguna` (`nama_pengguna`),
  UNIQUE KEY `surel` (`surel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: obat
-- ----------------------------
CREATE TABLE IF NOT EXISTS `obat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `gambar` VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
  `nama_obat` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `id_kategori` INT(11) DEFAULT NULL,
  `harga` INT(11) NOT NULL DEFAULT 0,
  `stok` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `fk_obat_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: keranjang
-- ----------------------------
CREATE TABLE IF NOT EXISTS `keranjang` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` INT(11) NOT NULL,
  `id_obat` INT(11) NOT NULL,
  `kuantitas` INT(11) NOT NULL DEFAULT 1,
  `harga_saat_ditambah` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id_pengguna` (`id_pengguna`),
  KEY `id_obat` (`id_obat`),
  CONSTRAINT `fk_keranjang_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_keranjang_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: pesanan
-- ----------------------------
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` INT(11) NOT NULL,
  `status` ENUM('tertunda', 'berhasil', 'gagal', 'selesai') NOT NULL DEFAULT 'tertunda',
  `harga_total` INT(11) NOT NULL DEFAULT 0,
  `alamat` TEXT NOT NULL,
  `nama_penerima` VARCHAR(100) NOT NULL,
  `surel_penerima` VARCHAR(100) NOT NULL,
  `nomor_telepon_penerima` VARCHAR(20) NOT NULL,
  `catatan` TEXT DEFAULT NULL,
  `waktu_dibuat` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_diubah` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pengguna` (`id_pengguna`),
  CONSTRAINT `fk_pesanan_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: barang_pesanan
-- ----------------------------
CREATE TABLE IF NOT EXISTS `barang_pesanan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `id_obat` INT(11) NOT NULL,
  `kuantitas` INT(11) NOT NULL DEFAULT 1,
  `waktu_dibuat` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_diubah` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_obat` (`id_obat`),
  CONSTRAINT `fk_barang_pesanan_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_barang_pesanan_obat` FOREIGN KEY (`id_obat`) REFERENCES `obat` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: pembayaran
-- ----------------------------
CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `bukti_pembayaran` VARCHAR(255) DEFAULT NULL,
  `waktu_dibuat` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_diubah` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pesanan` (`id_pesanan`),
  CONSTRAINT `fk_pembayaran_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table: riwayat_pesanan
-- ----------------------------
CREATE TABLE IF NOT EXISTS `riwayat_pesanan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `id_pengguna` INT(11) NOT NULL,
  `id_admin_yang_menyetujui` INT(11) NOT NULL,
  `status_saat_disetujui` VARCHAR(50) NOT NULL,
  `harga_total` INT(11) NOT NULL DEFAULT 0,
  `alamat` TEXT NOT NULL,
  `nama_penerima` VARCHAR(100) NOT NULL,
  `surel_penerima` VARCHAR(100) NOT NULL,
  `nomor_telepon_penerima` VARCHAR(20) NOT NULL,
  `catatan` TEXT DEFAULT NULL,
  `cuplikan_barang` JSON DEFAULT NULL,
  `waktu_dibuat_pesanan` DATETIME NOT NULL,
  `waktu_disetujui` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_pengguna` (`id_pengguna`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------
-- Seed Data
-- ----------------------------

-- Default admin account
-- Username: admin | Password: admin123
INSERT INTO `pengguna` (`nama_lengkap`, `nama_pengguna`, `surel`, `sandi`, `avatar`, `peran`, `waktu_dibuat`) VALUES
('Administrator', 'admin', 'admin@apotek-alifa.com', '$2y$12$gSw4r8H5wi0pV0KvmcJXD.9/mMShzALfC1Ct2d3k.7x8bEHUS5rLu', 'user-1.png', 'admin', NOW());

-- Sample categories
INSERT INTO `kategori` (`nama_kategori`) VALUES
('Obat Bebas'),
('Obat Keras'),
('Vitamin & Suplemen'),
('Obat Herbal'),
('Perawatan Luka'),
('Alat Kesehatan');

-- Sample products
INSERT INTO `obat` (`gambar`, `nama_obat`, `deskripsi`, `id_kategori`, `harga`, `stok`) VALUES
('default.jpg', 'Paracetamol 500mg', 'Obat pereda nyeri dan penurun demam. Digunakan untuk mengatasi sakit kepala, sakit gigi, nyeri otot, dan demam.', 1, 5000, 100),
('default.jpg', 'Amoxicillin 500mg', 'Antibiotik untuk mengatasi infeksi bakteri. Harus dengan resep dokter.', 2, 15000, 50),
('default.jpg', 'Vitamin C 1000mg', 'Suplemen vitamin C untuk menjaga daya tahan tubuh dan kesehatan kulit.', 3, 25000, 200),
('default.jpg', 'Antangin JRG', 'Obat herbal untuk meredakan masuk angin, mual, dan perut kembung.', 4, 8000, 150),
('default.jpg', 'Betadine 30ml', 'Antiseptik untuk membersihkan dan mencegah infeksi pada luka.', 5, 20000, 80),
('default.jpg', 'Tensimeter Digital', 'Alat pengukur tekanan darah digital yang mudah digunakan.', 6, 350000, 15);
