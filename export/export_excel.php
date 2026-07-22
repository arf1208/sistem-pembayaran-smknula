<?php
session_start();
// Menghubungkan ke database dengan naik 1 folder ke config
require_once __DIR__ . '/../config/database.php';

// Proteksi halaman: Pastikan user sudah login
if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login.php");
    exit();
}

// Set header agar browser langsung mendownload file ini sebagai Excel (.xls)
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pembayaran_SMKNU_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

try {
    // Ambil data riwayat transaksi pembayaran lengkap
    $query = "SELECT t.*, s.nama AS nama_siswa, s.kelas, j.nama_pembayaran, p.nama_lengkap AS nama_petugas 
              FROM transaksi t
              JOIN siswa s ON t.nis = s.nis
              JOIN jenis_pembayaran j ON t.id_jenis = j.id_jenis
              JOIN petugas p ON t.id_petugas = p.id_petugas
              ORDER BY t.tanggal_bayar DESC";
              
    $stmt = $pdo->query($query);
    $riwayat_pembayaran = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data untuk export: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000000;
            padding: 10px;
            font-size: 11pt;
        }
        th {
            background-color: #1F7A3E; /* Hijau Khas SMK NU */
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .title-box {
            text-align: center;
            margin-bottom: 25px;
        }
        .title-main {
            font-size: 16pt;
            font-weight: bold;
            color: #1F7A3E;
        }
        .title-sub {
            font-size: 11pt;
            color: #555555;
        }
    </style>
</head>
<body>

    <!-- Header Laporan di Excel -->
    <div class="title-box">
        <div class="title-main">LAPORAN TRANSAKSI PEMBAYARAN SISWA</div>
        <div class="title-main">SMK NU LAMONGAN</div>
        <div class="title-sub">Waktu Unduh: <?= date('d-m-Y H:i'); ?> WIB</div>
    </div>
    <br>

    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Transaksi</th>
                <th>No. Transaksi</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jenis Pembayaran</th>
                <th>Metode</th>
                <th>Nominal Bayar</th>
                <th>Denda</th>
                <th>Diskon</th>
                <th>Petugas Penerima</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($riwayat_pembayaran) > 0): ?>
                <?php $no = 1; foreach ($riwayat_pembayaran as $row): 
                    $tanggal = date('d-m-Y H:i', strtotime($row['tanggal_bayar']));
                    $diskon = isset($row['diskon']) ? $row['diskon'] : 0;
                    $denda = isset($row['denda']) ? $row['denda'] : 0;
                ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><?= $tanggal; ?> WIB</td>
                        <!-- Tanda kutip tunggal (') mencegah Excel mengubah format angka panjang menjadi scientific notation -->
                        <td class="text-center">'<?= htmlspecialchars($row['no_transaksi']); ?></td>
                        <td class="text-center">'<?= htmlspecialchars($row['nis']); ?></td>
                        <td><?= htmlspecialchars($row['nama_siswa']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['kelas']); ?></td>
                        <td><?= htmlspecialchars($row['nama_pembayaran']); ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['metode_pembayaran'] ?? 'Tunai'); ?></td>
                        <td class="text-end">Rp <?= number_format($row['nominal_bayar'], 0, ',', '.'); ?></td>
                        <td class="text-end" style="color: #c0392b;">Rp <?= number_format($denda, 0, ',', '.'); ?></td>
                        <td class="text-end" style="color: #2980b9;">Rp <?= number_format($diskon, 0, ',', '.'); ?></td>
                        <td><?= htmlspecialchars($row['nama_petugas']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; font-style: italic; color: #888888;">
                        Belum ada data transaksi pembayaran yang tercatat.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>