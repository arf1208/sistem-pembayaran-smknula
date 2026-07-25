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

// Bersihkan string nominal bayar dari format titik/koma/Rupiah sebelum divalidasi ke float
$raw_nominal = $_POST['nominal_bayar'] ?? '0';
$clean_nominal = str_replace(['Rp', '.', ' ', ','], ['', '', '', '.'], $raw_nominal);
$nominal_bayar = filter_var($clean_nominal, FILTER_VALIDATE_FLOAT);

$diskon = filter_input(INPUT_POST, 'diskon', FILTER_VALIDATE_FLOAT) ?: 0.00;
$denda = filter_input(INPUT_POST, 'denda', FILTER_VALIDATE_FLOAT) ?: 0.00;
$metode = filter_input(INPUT_POST, 'metode_pembayaran', FILTER_SANITIZE_SPECIAL_CHARS);
$status_bayar = filter_input(INPUT_POST, 'status_bayar', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Lunas';
$catatan = filter_input(INPUT_POST, 'catatan', FILTER_SANITIZE_SPECIAL_CHARS);
$keterangan = filter_input(INPUT_POST, 'keterangan', FILTER_SANITIZE_SPECIAL_CHARS);
$id_petugas = $_SESSION['id_petugas'] ?? null;

if (!$nis || !$id_jenis || !$id_petugas || $nominal_bayar === false) {
    echo json_encode(['status' => 'error', 'message' => 'Data parameter transaksi tidak lengkap atau nominal tidak valid.']);
    exit;
}

try {
    // Validasi apakah jenis pembayaran ada
    $stmt_jenis = $pdo->prepare("SELECT id_jenis FROM jenis_pembayaran WHERE id_jenis = ?");
    $stmt_jenis->execute([$id_jenis]);
    if (!$stmt_jenis->fetch()) {
        throw new Exception("Jenis pembayaran tidak valid.");
    }

    // Hitung total akhir
    $total_akhir = ($nominal_bayar - $diskon) + $denda;

    // Generate Nomor Transaksi Unik: TR-SMKNU-YYYYMMDD-XXXX
    $tanggal_hari_ini = date('Ymd');
    $stmt_counter = $pdo->query("SELECT COUNT(*) FROM transaksi WHERE tanggal_bayar = CURDATE()");
    $counter = $stmt_counter->fetchColumn() + 1;
    $no_transaksi = "TR-SMKNU-" . $tanggal_hari_ini . "-" . str_pad($counter, 4, '0', STR_PAD_LEFT);

    // Mulai Database Transaction (Atomic)
    $pdo->beginTransaction();

    $stmt_insert = $pdo->prepare("INSERT INTO transaksi 
        (no_transaksi, nis, id_jenis, nominal_bayar, diskon, denda, total_akhir, status_bayar, catatan, metode_pembayaran, keterangan, tanggal_bayar, jam_bayar, id_petugas) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME(), ?)");

    $stmt_insert->execute([
        $no_transaksi,
        $nis,
        $id_jenis,
        $nominal_bayar,
        $diskon,
        $denda,
        $total_akhir,
        $status_bayar,
        $catatan,
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