<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$nis = $_GET['nis'] ?? '';

if (empty($nis)) {
    echo json_encode([]);
    exit;
}

try {
    // Ambil jenis pembayaran yang belum lunas sepenuhnya oleh siswa tersebut
    $query = "SELECT j.* 
              FROM jenis_pembayaran j
              WHERE j.id_jenis NOT IN (
                  SELECT t.id_jenis 
                  FROM transaksi t 
                  WHERE t.nis = ? AND t.status_bayar = 'Lunas'
              )";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$nis]);
    $tagihan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tagihan);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}