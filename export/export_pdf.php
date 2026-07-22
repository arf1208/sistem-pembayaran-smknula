<?php
session_start();
// Menghubungkan ke database dengan naik 1 folder ke config
require_once __DIR__ . '/../config/database.php';

// Proteksi halaman: Pastikan user sudah login
if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login.php");
    exit();
}

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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_Pembayaran_SMKNU_<?= date('Y-m-d'); ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
        }
        /* Desain Header Laporan */
        .header-container {
            display: flex;
            align-items: center;
            border-bottom: 3px double #1F7A3E;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .logo-placeholder {
            width: 70px;
            height: 70px;
            background-color: #1F7A3E;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            margin-right: 15px;
        }
        .header-text {
            flex-grow: 1;
        }
        .header-text h2 {
            margin: 0;
            font-size: 18px;
            color: #1F7A3E;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 3px 0 0 0;
            font-size: 11px;
            color: #555;
        }
        
        /* Info Metadata */
        .meta-info {
            margin-bottom: 15px;
            font-size: 11px;
            color: #444;
        }

        /* Tabel Data */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
        }
        th {
            background-color: #1F7A3E !important;
            color: white !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        
        /* Tanda Tangan */
        .footer-sign {
            float: right;
            text-align: center;
            margin-top: 20px;
            width: 200px;
        }
        .footer-sign p {
            margin: 0;
        }
        .space-sign {
            height: 70px;
        }

        /* Pengaturan Cetak PDF (A4) */
        @media print {
            body {
                padding: 0;
                background-color: #fff;
            }
            /* Menyembunyikan tombol print bawaan browser jika ada */
            .no-print {
                display: none;
            }
            @page {
                size: A4 landscape; /* Cetak posisi tidur agar muat banyak kolom */
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

    <!-- Header Dokumen -->
    <div class="header-container">
        <div class="logo-placeholder">SMK NU</div>
        <div class="header-text">
            <h2>SMK NU LAMONGAN</h2>
            <p>Sistem Informasi Pembayaran Sekolah (Administrasi & Kasir)</p>
            <p style="font-size: 10px; font-style: italic;">Jl. Raya Lamongan, Lamongan, Jawa Timur</p>
        </div>
    </div>

    <!-- Info Dokumen -->
    <div class="meta-info">
        <strong>Laporan:</strong> Riwayat Transaksi Pembayaran Siswa<br>
        <strong>Tanggal Cetak:</strong> <?= date('d-m-Y H:i'); ?> WIB<br>
        <strong>Status Data:</strong> Terverifikasi Sistem
    </div>

    <!-- Tabel Riwayat -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 12%;">No. Transaksi</th>
                <th style="width: 10%;">NIS</th>
                <th style="width: 15%;">Nama Siswa</th>
                <th class="text-center" style="width: 5%;">Kelas</th>
                <th style="width: 15%;">Jenis Pembayaran</th>
                <th class="text-end" style="width: 10%;">Nominal</th>
                <th class="text-end" style="width: 10%;">Potongan/Denda</th>
                <th style="width: 8%;">Petugas</th>
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
                        <td><?= $tanggal; ?> WIB</td>
                        <td><?= htmlspecialchars($row['no_transaksi']); ?></td>
                        <td><?= htmlspecialchars($row['nis']); ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_siswa']); ?></strong></td>
                        <td class="text-center"><?= htmlspecialchars($row['kelas']); ?></td>
                        <td><?= htmlspecialchars($row['nama_pembayaran']); ?></td>
                        <td class="text-end" style="font-weight: bold;">Rp <?= number_format($row['nominal_bayar'], 0, ',', '.'); ?></td>
                        <td class="text-end" style="font-size: 9px;">
                            <span style="color: #c0392b;">D: Rp <?= number_format($denda, 0, ',', '.'); ?></span><br>
                            <span style="color: #2980b9;">P: Rp <?= number_format($diskon, 0, ',', '.'); ?></span>
                        </td>
                        <td><?= htmlspecialchars($row['nama_petugas']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; font-style: italic; color: #888;">
                        Belum ada data transaksi pembayaran yang tercatat.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="footer-sign">
        <p>Lamongan, <?= date('d F Y'); ?></p>
        <p>Petugas Administrasi,</p>
        <div class="space-sign"></div>
        <p><strong><u><?= $_SESSION['nama_lengkap'] ?? 'Administrator'; ?></u></strong></p>
    </div>

    <!-- Script Otomatis Membuka Jendela Print/Save PDF -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Memberikan jeda 500ms agar browser selesai me-render CSS sebelum mencetak
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>