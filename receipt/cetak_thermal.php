<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['id_petugas'])) {
    die("Akses Ilegal Terdeteksi.");
}

$no_transaksi = filter_input(INPUT_GET, 'no_transaksi', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$no_transaksi) {
    die("Nomor transaksi tidak ditemukan.");
}

// Ambil data transaksi super lengkap
$query = "SELECT t.*, s.nama_lengkap, s.kelas, s.jurusan, jp.nama_pembayaran, p.nama_lengkap AS nama_petugas, 
                 inst.nama_sekolah, inst.alamat AS alamat_sekolah, inst.telepon AS telp_sekolah, inst.footer_struk
          FROM transaksi t
          JOIN siswa s ON t.nis = s.nis
          JOIN jenis_pembayaran jp ON t.id_jenis = jp.id_jenis
          JOIN petugas p ON t.id_petugas = p.id_petugas
          CROSS JOIN pengaturan inst
          WHERE t.no_transaksi = ? LIMIT 1";

$stmt = $pdo->prepare($query);
$stmt->execute([$no_transaksi]);
$tx = $stmt->fetch();

if (!$tx) {
    die("Detail riwayat transaksi tidak terdaftar.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Struk #<?= $tx['no_transaksi'] ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Mendukung printer POS58 secara default. Untuk 80mm ubah ke 80mm */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .header-title { font-weight: bold; font-size: 14px; margin: 0; }
        .metadata table { width: 100%; font-size: 11px; }
        .items table { width: 100%; margin-top: 5px; }
        .footer-note { font-size: 10px; margin-top: 15px; font-style: italic; }
        
        /* Mengontrol Aksi Cetak Browser Otomatis */
        @media print {
            html, body { width: 58mm; margin: 0; padding: 5px; }
            .no-print { display: none; }
        }
        .btn-print {
            padding: 5px 15px;
            background: #1F7A3E;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Panel Navigasi Cetak Manual Sebelum Print Otomatis -->
<div class="no-print" style="background:#f1f1f1; padding:10px; margin-bottom:15px; text-align:center; width:100%;">
    <button class="btn-print" onclick="window.print();">CETAK STRUK</button>
</div>

<!-- AREA STRUK THERMAL -->
<div class="text-center">
    <p class="header-title"><?= strtoupper(htmlentities($tx['nama_sekolah'])) ?></p>
    <small><?= htmlentities($tx['alamat_sekolah']) ?><br>Telp: <?= htmlentities($tx['telp_sekolah']) ?></small>
</div>

<div class="divider"></div>

<div class="metadata">
    <table>
        <tr><td>No. TX</td><td>: <?= $tx['no_transaksi'] ?></td></tr>
        <tr><td>Tanggal</td><td>: <?= $tx['style_tgl'] ?? $tx['tanggal_bayar'] ?> <?= $tx['jam_bayar'] ?></td></tr>
        <tr><td>Siswa</td><td>: <?= htmlentities($tx['nama_lengkap']) ?></td></tr>
        <tr><td>Kelas</td><td>: <?= $tx['kelas'] ?> - <?= $tx['jurusan'] ?></td></tr>
        <tr><td>Petugas</td><td>: <?= htmlentities($tx['nama_petugas']) ?></td></tr>
    </table>
</div>

<div class="divider"></div>

<div class="items">
    <table cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="2"><b><?= htmlentities($tx['nama_pembayaran']) ?></b></td>
        </tr>
        <tr>
            <td>Nominal Utama</td>
            <td class="text-right">Rp <?= number_format($tx['nominal_bayar'], 0, ',', '.') ?></td>
        </tr>
        <?php if ($tx['diskon'] > 0): ?>
        <tr>
            <td>Diskon/Potongan</td>
            <td class="text-right">-Rp <?= number_format($tx['diskon'], 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($tx['denda'] > 0): ?>
        <tr>
            <td>Denda Terlambat</td>
            <td class="text-right">+Rp <?= number_format($tx['denda'], 0, ',', '.') ?></td>
        </tr>
        <?php endif; ?>
        <tr><td colspan="2"><div class="divider" style="margin:4px 0;"></div></td></tr>
        <tr style="font-weight: bold; font-size: 13px;">
            <td>TOTAL AKHIR</td>
            <td class="text-right">Rp <?= number_format($tx['total_akhir'], 0, ',', '.') ?></td>
        </tr>
        <tr>
            <td>Metode Bayar</td>
            <td class="text-right"><b><?= $tx['metode_pembayaran'] ?></b></td>
        </tr>
    </table>
</div>

<div class="divider"></div>

<div class="text-center footer-note">
    <p><?= htmlentities($tx['footer_struk']) ?></p>
    <small style="font-size: 8px; color: #555;">Sistem Pembayaran digital SMKNULA 2026</small>
</div>

<script>
    // Memicu printer driver langsung sesaat setelah window dimuat sempurna
    window.onload = function() {
        window.print();
    }
</script>
</body>
</html>