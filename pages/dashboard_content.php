<?php
if (!isset($_SESSION['id_petugas'])) {
    exit('Akses langsung diblokir.');
}

// 1. Kueri Ringkasan Keuangan & Statistik (Tahun Berjalan 2026)
$today = date('Y-m-d');
$month = date('m');
$year  = date('Y');

// Pendapatan Hari Ini
$stmt = $pdo->prepare("SELECT SUM(total_akhir) FROM transaksi WHERE tanggal_bayar = ?");
$stmt->execute([$today]);
$pendapatan_hari_ini = $stmt->fetchColumn() ?: 0;

// Pendapatan Bulan Ini
$stmt = $pdo->prepare("SELECT SUM(total_akhir) FROM transaksi WHERE MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?");
$stmt->execute([$month, $year]);
$pendapatan_bulan_ini = $stmt->fetchColumn() ?: 0;

// Pendapatan Tahun Ini
$stmt = $pdo->prepare("SELECT SUM(total_akhir) FROM transaksi WHERE YEAR(tanggal_bayar) = ?");
$stmt->execute([$year]);
$pendapatan_tahun_ini = $stmt->fetchColumn() ?: 0;

// Total Siswa Terdaftar
$total_siswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn() ?: 0;

// Total Seluruh Transaksi
$total_transaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn() ?: 0;

// Kueri 5 Aktivitas Transaksi Terbaru (Disesuaikan ke nama_lengkap)
$query_latest = "SELECT t.no_transaksi, s.nama_lengkap, jp.nama_pembayaran, t.total_akhir, t.jam_bayar 
                 FROM transaksi t 
                 JOIN siswa s ON t.nis = s.nis 
                 JOIN jenis_pembayaran jp ON t.id_jenis = jp.id_jenis 
                 ORDER BY t.tanggal_bayar DESC, t.jam_bayar DESC LIMIT 5";
$latest_activities = $pdo->query($query_latest)->fetchAll();
?>

<!-- ROW METRIK UTAMA (CARD) -->
<div class="row mb-4" data-aos="fade-up">
    <!-- Hari Ini -->
    <div class="col-md-4 mb-3">
        <div class="card card-custom p-3 border-gold text-white" style="background: linear-gradient(135deg, #1F7A3E 0%, #15a545 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-light text-uppercase fw-bold" style="font-size: 11px;">Pendapatan Hari Ini</small>
                    <h3 class="fw-bold m-0 mt-1">Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 opacity-50"><i class="fa-solid fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>
    <!-- Bulan Ini -->
    <div class="col-md-4 mb-3">
        <div class="card card-custom p-3 border-gold" style="background: #ffffff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Pendapatan Bulan Ini</small>
                    <h3 class="fw-bold text-success m-0 mt-1">Rp <?= number_format($pendapatan_bulan_ini, 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-wallet"></i></div>
            </div>
        </div>
    </div>
    <!-- Tahun Ini -->
    <div class="col-md-4 mb-3">
        <div class="card card-custom p-3 border-gold" style="background: #ffffff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Pendapatan Tahun Ini</small>
                    <h3 class="fw-bold text-dark m-0 mt-1">Rp <?= number_format($pendapatan_tahun_ini, 0, ',', '.') ?></h3>
                </div>
                <div class="fs-1 text-warning opacity-50"><i class="fa-solid fa-coins"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4" data-aos="fade-up" data-aos-delay="100">
    <div class="col-md-6 mb-3">
        <div class="card card-custom p-3 text-center">
            <span class="text-muted small fw-bold">TOTAL SISWA TERDAFTAR</span>
            <h2 class="fw-bold text-success mt-1 mb-0"><i class="fa-solid fa-graduation-cap me-2"></i><?= $total_siswa ?> Siswa</h2>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card card-custom p-3 text-center">
            <span class="text-muted small fw-bold">TOTAL TRANSAKSI DIPROSES</span>
            <h2 class="fw-bold text-dark mt-1 mb-0"><i class="fa-solid fa-file-invoice me-2"></i><?= $total_transaksi ?> Transaksi</h2>
        </div>
    </div>
</div>

<!-- ROW GRAFIK & AKTIVITAS -->
<div class="row">
    <!-- Area Chart.js -->
    <div class="col-lg-7 mb-4" data-aos="fade-right" data-aos-delay="200">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-chart-line me-2"></i>Grafik Pembayaran Bulanan (<?= $year ?>)</h6>
            <div style="position: relative; height:260px;">
                <canvas id="chartPendapatan"></canvas>
            </div>
        </div>
    </div>

    <!-- Aktivitas Pembayaran Terakhir -->
    <div class="col-lg-5 mb-4" data-aos="fade-left" data-aos-delay="200">
        <div class="card card-custom p-4">
            <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-clock-rotate-left me-2"></i>Aktivitas Transaksi Terbaru</h6>
            <div class="list-group list-group-flush">
                <?php if (empty($latest_activities)): ?>
                    <p class="text-muted small text-center py-4">Belum ada aktivitas transaksi hari ini.</p>
                <?php else: ?>
                    <?php foreach ($latest_activities as $act): ?>
                        <div class="list-group-item px-0 py-2 bg-transparent border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold small d-block"><?= htmlentities($act['nama_lengkap']) ?></span>
                                    <small class="text-muted"><?= htmlentities($act['nama_pembayaran']) ?> • <?= $act['jam_bayar'] ?></small>
                                </div>
                                <span class="badge bg-light text-success border border-success fw-bold">
                                    Rp <?= number_format($act['total_akhir'], 0, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- LOGIKAL GRAFIK CHART.JS -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('chartPendapatan').getContext('2d');
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Total Pendapatan (Rp)',
                    data: [0, 0, 0, 0, 0, 0, <?= $pendapatan_bulan_ini ?>, 0, 0, 0, 0, 0],
                    backgroundColor: '#1F7A3E',
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
</script>