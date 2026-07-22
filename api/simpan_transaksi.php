<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}

// Validasi Token CSRF
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    echo json_encode(['status' => 'error', 'message' => 'CSRF Token mismatch']);
    exit;
}

$nis = filter_input(INPUT_POST, 'nis', FILTER_SANITIZE_SPECIAL_CHARS);
$id_jenis = filter_input(INPUT_POST, 'id_jenis', FILTER_VALIDATE_INT);
$diskon = filter_input(INPUT_POST, 'diskon', FILTER_VALIDATE_FLOAT) ?: 0.00;
$denda = filter_input(INPUT_POST, 'denda', FILTER_VALIDATE_FLOAT) ?: 0.00;
$metode = filter_input(INPUT_POST, 'metode_pembayaran', FILTER_SANITIZE_SPECIAL_CHARS);
$keterangan = filter_input(INPUT_POST, 'keterangan', FILTER_SANITIZE_SPECIAL_CHARS);
$id_petugas = $_SESSION['id_petugas'] ?? null;

if (!$nis || !$id_jenis || !$id_petugas) {
    echo json_encode(['status' => 'error', 'message' => 'Data parameter transaksi tidak lengkap.']);
    exit;
}

try {
    // Cari nominal utama tagihan
    $stmt_jenis = $pdo->prepare("SELECT nominal FROM jenis_pembayaran WHERE id_jenis = ?");
    $stmt_jenis->execute([$id_jenis]);
    $nominal_pembayaran = $stmt_jenis->fetchColumn();

    if (!$nominal_pembayaran) {
        throw new Exception("Jenis pembayaran tidak valid.");
    }

    // Generate Nomor Transaksi Unik: TR-SMKNU-YYYYMMDD-XXXX
    $tanggal_hari_ini = date('Ymd');
    $stmt_counter = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE tanggal_bayar = CURDATE()");
    $counter = $stmt_counter->fetchColumn() + 1;
    $no_transaksi = "TR-SMKNU-" . $tanggal_hari_ini . "-" . str_pad($counter, 4, '0', STR_PAD_LEFT);

    // Mulai Database Transaction (Atomic)
    $pdo->beginTransaction();

    $stmt_insert = $pdo->prepare("INSERT INTO transaksi 
        (no_transaksi, nis, id_jenis, nominal_bayar, diskon, denda, metode_pembayaran, keterangan, tanggal_bayar, jam_bayar, id_petugas) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?)");

    $stmt_insert->execute([
        $no_transaksi,
        $nis,
        $id_jenis,
        $nominal_pembayaran,
        $diskon,
        $denda,
        $metode,
        $keterangan,
        $id_petugas
    ]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'no_transaksi' => $no_transaksi]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
}