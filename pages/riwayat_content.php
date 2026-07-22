<?php
// Mengambil data riwayat transaksi pembayaran dari database dengan relasi ke tabel siswa, jenis_pembayaran, dan petugas
try {
    $query = "SELECT t.*, s.nama_lengkap AS nama_siswa, s.kelas, j.nama_pembayaran, p.nama_lengkap AS nama_petugas 
              FROM transaksi t
              JOIN siswa s ON t.nis = s.nis
              JOIN jenis_pembayaran j ON t.id_jenis = j.id_jenis
              JOIN petugas p ON t.id_petugas = p.id_petugas
              ORDER BY t.tanggal_bayar DESC";
              
    $stmt = $pdo->query($query);
    $riwayat_pembayaran = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data riwayat pembayaran: " . $e->getMessage());
}
?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success m-0" style="font-family: 'Poppins', sans-serif;">
            <i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat Pembayaran
        </h3>
        <div>
            <!-- Link diperbaiki mengarah ke dalam folder export/ -->
            <a href="export/export_excel.php" target="_blank" class="btn btn-outline-success btn-sm fw-bold me-2 px-3">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </a>
            <a href="export/export_pdf.php" target="_blank" class="btn btn-outline-danger btn-sm fw-bold px-3">
                <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Card Wrapper -->
    <div class="card shadow-sm border-0" style="border-radius: 15px; background: #ffffff;">
        <div class="card-body p-3">
            <div class="table-responsive">
                <!-- Ditambahkan kelas table-bordered untuk garis sekat persegi panjang antar kolom & baris -->
                <table class="table table-bordered table-hover align-middle small" style="min-width: 1000px;">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-3 text-center text-secondary fw-bold" style="width: 5%;">No</th>
                            <th class="py-3 px-3 text-start text-secondary fw-bold" style="width: 15%;">Tanggal & Waktu</th>
                            <th class="py-3 px-3 text-start text-secondary fw-bold" style="width: 15%;">Nama Siswa</th>
                            <th class="py-3 px-3 text-center text-secondary fw-bold" style="width: 8%;">Kelas</th>
                            <th class="py-3 px-3 text-start text-secondary fw-bold" style="width: 18%;">Jenis Pembayaran</th>
                            <th class="py-3 px-3 text-end text-secondary fw-bold" style="width: 12%;">Jumlah Bayar</th>
                            <th class="py-3 px-3 text-end text-secondary fw-bold" style="width: 12%;">Potongan / Denda</th>
                            <th class="py-3 px-3 text-start text-secondary fw-bold" style="width: 10%;">Petugas</th>
                            <th class="py-3 px-3 text-center text-secondary fw-bold" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($riwayat_pembayaran) > 0): ?>
                            <?php $no = 1; foreach ($riwayat_pembayaran as $row): 
                                $tanggal = date('d-m-Y', strtotime($row['tanggal_bayar']));
                                $waktu = date('H:i', strtotime($row['tanggal_bayar']));
                                $diskon = isset($row['diskon']) ? $row['diskon'] : 0;
                                $denda = isset($row['denda']) ? $row['denda'] : 0;
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted px-3 py-2"><?= $no++; ?></td>
                                    <td class="px-3 py-2">
                                        <div class="fw-semibold text-dark"><?= $tanggal; ?></div>
                                        <small class="text-muted" style="font-size: 11px;"><i class="fa-regular fa-clock me-1"></i><?= $waktu; ?> WIB</small>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="fw-bold text-success"><?= htmlspecialchars($row['nama_siswa']); ?></div>
                                        <small class="text-muted" style="font-size: 11px;">NIS: <?= htmlspecialchars($row['nis']); ?></small>
                                    </td>
                                    <td class="text-center px-3 py-2">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($row['kelas']); ?></span>
                                    </td>
                                    <td class="fw-semibold text-dark px-3 py-2"><?= htmlspecialchars($row['nama_pembayaran']); ?></td>
                                    <td class="text-end fw-bold text-success px-3 py-2">
                                        Rp <?= number_format($row['nominal_bayar'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-end text-muted px-3 py-2" style="line-height: 1.4;">
                                        <span class="text-danger">Denda: Rp <?= number_format($denda, 0, ',', '.'); ?></span><br>
                                        <span class="text-primary">Diskon: Rp <?= number_format($diskon, 0, ',', '.'); ?></span>
                                    </td>
                                    <td class="text-muted px-3 py-2"><?= htmlspecialchars($row['nama_petugas']); ?></td>
                                    <td class="text-center px-3 py-2">
                                        <a href="receipt/cetak_thermal.php?id=<?= $row['no_transaksi']; ?>" target="_blank" class="btn btn-sm btn-light border text-success fw-bold px-2 py-1" style="font-size: 12px;" title="Cetak Struk">
                                            <i class="fa-solid fa-print me-1"></i> Struk
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <i class="fa-solid fa-folder-open fs-2 text-secondary opacity-50"></i>
                                    </div>
                                    <span class="fw-semibold text-secondary" style="font-size: 14px;">Belum ada transaksi pembayaran yang tercatat.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>