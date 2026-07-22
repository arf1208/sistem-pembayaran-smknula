<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // Ubah ke true jika menggunakan HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
    ]);
}

// Proteksi CSRF Token Generator
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$host = 'localhost';
$db   = 'sistem_pembayaran_smknula';
$user = 'root';
$pass = ''; // Sesuaikan dengan kredensial database lokal Anda
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3307";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Koneksi Database Gagal: " . $e->getMessage());
}
?>