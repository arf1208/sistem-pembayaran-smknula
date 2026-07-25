<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_petugas'])) {
    die("Akses Ilegal Terdeteksi.");
}

$no_transaksi = filter_input(INPUT_GET, 'no_transaksi', FILTER_SANITIZE_SPECIAL_CHARS);

if ($no_transaksi) {
    try {
        $stmt = $pdo->prepare("DELETE FROM transaksi WHERE no_transaksi = ?");
        $stmt->execute([$no_transaksi]);
    } catch (PDOException $e) {
        // Handle error jika diperlukan
    }
}

// Redirect kembali ke halaman riwayat
header("Location: ../dashboard.php?page=riwayat");
exit;