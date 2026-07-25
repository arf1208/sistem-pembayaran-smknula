<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_petugas'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.']);
    exit;
}

$keyword = filter_input(INPUT_GET, 'nis', FILTER_SANITIZE_SPECIAL_CHARS);

try {
    // 1. Cari data siswa
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nis = ? OR nama_lengkap LIKE ? LIMIT 1");
    $stmt->execute([$keyword, "%$keyword%"]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($siswa) {
        // 2. Ambil riwayat transaksi
        $stmt_tx = $pdo->prepare("SELECT t.tanggal_bayar, jp.nama_pembayaran, t.total_akhir 
                                  FROM transaksi t 
                                  JOIN jenis_pembayaran jp ON t.id_jenis = jp.id_jenis 
                                  WHERE t.nis = ? ORDER BY t.tanggal_bayar DESC LIMIT 5");
        $stmt_tx->execute([$siswa['nis']]);
        $riwayat = $stmt_tx->fetchAll(PDO::FETCH_ASSOC);

        // 3. Ambil jenis pembayaran dan hitung sisa tagihan jika ada cicilan sebelumnya, 
        // serta kecualikan yang sudah berstatus Lunas sepenuhnya.
        $stmt_jp = $pdo->prepare("
            SELECT jp.id_jenis, jp.nama_pembayaran, jp.nominal, 
                   (jp.nominal - COALESCE(SUM(t.total_akhir), 0)) AS sisa_tagihan
            FROM jenis_pembayaran jp
            LEFT JOIN transaksi t ON jp.id_jenis = t.id_jenis AND t.nis = ?
            WHERE jp.tahun_ajaran LIKE ?
            GROUP BY jp.id_jenis, jp.nama_pembayaran, jp.nominal
            HAVING sisa_tagihan > 0
        ");
        $stmt_jp->execute([$siswa['nis'], '%' . trim($siswa['tahun_ajaran']) . '%']);
        $jenis_pembayaran = $stmt_jp->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'nis' => $siswa['nis'],
                'nama_lengkap' => $siswa['nama_lengkap'],
                'kelas' => $siswa['kelas'],
                'jurusan' => $siswa['jurusan'],
                'foto' => $siswa['foto'] ?? 'default.png',
                'riwayat' => $riwayat
            ],
            'jenis_pembayaran' => $jenis_pembayaran
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Siswa tidak ditemukan.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
}