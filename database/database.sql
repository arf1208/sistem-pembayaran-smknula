CREATE DATABASE IF NOT EXISTS sistem_pembayaran_smknula;
USE sistem_pembayaran_smknula;

-- 1. TABEL SETTING INSTANSI
CREATE TABLE IF NOT EXISTS pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_sekolah VARCHAR(150) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    tahun_ajaran VARCHAR(9) DEFAULT '2026/2027',
    rekening VARCHAR(50),
    footer_struk VARCHAR(255),
    logo VARCHAR(255) DEFAULT 'logo-smknu.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABEL PETUGAS (Password default: 'admin123' terenkripsi password_hash)
CREATE TABLE IF NOT EXISTS petugas (
    id_petugas INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas') NOT NULL DEFAULT 'petugas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL SISWA
CREATE TABLE IF NOT EXISTS siswa (
    nis VARCHAR(20) PRIMARY KEY,
    nisn VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    jk ENUM('L', 'P') NOT NULL,
    kelas VARCHAR(10) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    hp_ortu VARCHAR(15),
    alamat TEXT,
    foto VARCHAR(255) DEFAULT 'default.png',
    INDEX idx_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL JENIS PEMBAYARAN
CREATE TABLE IF NOT EXISTS jenis_pembayaran (
    id_jenis INT AUTO_INCREMENT PRIMARY KEY,
    nama_pembayaran VARCHAR(50) NOT NULL,
    nominal DECIMAL(10,2) NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    INDEX idx_tahun (tahun_ajaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL TRANSAKSI (KASIR)
CREATE TABLE IF NOT EXISTS transaksi (
    no_transaksi VARCHAR(30) PRIMARY KEY,
    nis VARCHAR(20) NOT NULL,
    id_jenis INT NOT NULL,
    nominal_bayar DECIMAL(10,2) NOT NULL,
    diskon DECIMAL(10,2) DEFAULT 0.00,
    denda DECIMAL(10,2) DEFAULT 0.00,
    total_akhir DECIMAL(10,2) GENERATED ALWAYS AS ((nominal_bayar - diskon) + denda) STORED,
    metode_pembayaran ENUM('Tunai', 'Transfer', 'QRIS') NOT NULL,
    keterangan TEXT,
    tanggal_bayar DATE NOT NULL,
    jam_bayar TIME NOT NULL,
    id_petugas INT NOT NULL,
    FOREIGN KEY (nis) REFERENCES siswa(nis) ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_jenis) REFERENCES jenis_pembayaran(id_jenis) ON DELETE RESTRICT,
    FOREIGN KEY (id_petugas) REFERENCES petugas(id_petugas) ON DELETE RESTRICT,
    INDEX idx_tanggal (tanggal_bayar),
    INDEX idx_nis (nis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- DUMMY DATA SEEDERS
INSERT INTO pengaturan (nama_sekolah, alamat, telepon, email, tahun_ajaran, rekening, footer_struk) VALUES 
('SMK NU Lamongan', 'Jl. Panglima Sudirman No. C7, Lamongan', '0322-123456', 'info@smknulamongan.sch.id', '2026/2027', 'BRI 0012-01-002345-53-1 a.n SMK NU', 'Terima Kasih Atas Pembayaran Anda. Semoga Berkah.');

INSERT INTO petugas (username, password, nama_lengkap, role) VALUES 
('admin', '$2y$10$iM9kKx2w17g.qNlqA1/ZkeWcW.z7R3GfV0vH8p3Z1W8hA/4oGomgW', 'Hj. Siti Aminah, S.Pd', 'admin'),
('petugas_tu', '$2y$10$iM9kKx2w17g.qNlqA1/ZkeWcW.z7R3GfV0vH8p3Z1W8hA/4oGomgW', 'Ahmad Fauzi', 'petugas');

INSERT INTO siswa (nis, nisn, nama, jk, kelas, jurusan, tahun_ajaran, hp_ortu, alamat) VALUES 
('26271001', '0081234561', 'Muhammad Reyhan', 'L', 'XI', 'TKJ (Teknik Komputer Jaringan)', '2026/2027', '081234567890', 'Lamongan Kota'),
('26271002', '0081234562', 'Siti Fatimah', 'P', 'X', 'RPL (Rekayasa Perangkat Lunak)', '2026/2027', '081234567891', 'Babat, Lamongan');

INSERT INTO jenis_pembayaran (nama_pembayaran, nominal, tahun_ajaran) VALUES 
('SPP Juli 2026', 250000.00, '2026/2027'),
('Daftar Ulang', 1200000.00, '2026/2027'),
('Seragam Olahraga', 450000.00, '2026/2027');