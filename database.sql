-- Database Pengelolaan Warung Turnip
CREATE DATABASE IF NOT EXISTS toko_management;
USE toko_management;

-- Tabel Admin/Pemilik
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Barang
CREATE TABLE kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Barang
CREATE TABLE barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_barang VARCHAR(50) NOT NULL UNIQUE,
    nama_barang VARCHAR(150) NOT NULL,
    kategori_id INT NOT NULL,
    harga_beli DECIMAL(15, 2) NOT NULL,
    harga_jual DECIMAL(15, 2) NOT NULL,
    stok INT DEFAULT 0,
    satuan VARCHAR(50),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
);

-- Tabel Barang Masuk
CREATE TABLE barang_masuk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barang_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(15, 2),
    total_harga DECIMAL(15, 2),
    keterangan TEXT,
    admin_id INT NOT NULL,
    tanggal_masuk DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (admin_id) REFERENCES admin(id)
);

-- Tabel Penjualan (Barang Keluar)
CREATE TABLE penjualan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barang_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(15, 2),
    total_harga DECIMAL(15, 2),
    nama_pembeli VARCHAR(100),
    admin_id INT NOT NULL,
    tanggal_penjualan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (admin_id) REFERENCES admin(id)
);

-- Insert admin default
INSERT INTO admin (username, password, nama_lengkap, email) 
VALUES ('admin', 'admin123', 'Admin Warung Turnip', 'admin@toko.com');

-- Insert kategori default
INSERT INTO kategori (nama_kategori) VALUES 
('Sembako'),
('Minuman'),
('Makanan Ringan'),
('Kebersihan'),
('ATK');
