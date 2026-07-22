<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- SIDEBAR UTAMA (Otomatis tertutup/collapsed saat pertama kali masuk) -->
<nav id="sidebar" class="sidebar bg-success text-white p-3 collapsed d-flex flex-column justify-content-between" style="min-height: 100vh; width: 260px; transition: all 0.3s ease-in-out; white-space: nowrap; overflow: hidden; position: sticky; top: 0;">
    
    <div>
        <!-- Bagian Logo & Identitas -->
        <div class="text-center my-4 brand-container">
            <img src="assets/images/logo-smknu.png" alt="Logo SMK NU" class="img-fluid mb-2 logo-img" style="max-width: 65px; background: white; border-radius: 50%; padding: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.15);" onerror="this.src='https://www.image2url.com/r2/default/files/1784209762264-dc5b6327-3897-4e36-b9f1-200ff68df13f.png'">
            
            <div class="sidebar-text-content">
                <h6 class="fw-bold text-white m-0">SMK NU LAMONGAN</h6>
                <small class="text-warning small" style="font-size: 11px;">KASIR & ADMINISTRASI</small>
            </div>
        </div>

        <hr class="text-white">

        <!-- Menu Navigasi -->
        <ul class="nav flex-column gap-1">
            <li class="nav-item mb-1">
                <a href="dashboard.php?page=dashboard" class="nav-link text-white <?= $currentPage === 'dashboard' ? 'active bg-white text-success fw-bold rounded' : '' ?>" title="Dashboard">
                    <i class="fa-solid fa-gauge me-2"></i> <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="dashboard.php?page=kasir" class="nav-link text-white <?= $currentPage === 'kasir' ? 'active bg-white text-success fw-bold rounded' : '' ?>" title="Transaksi Kasir">
                    <i class="fa-solid fa-cash-register me-2"></i> <span class="sidebar-text">Transaksi Kasir</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="dashboard.php?page=siswa" class="nav-link text-white <?= $currentPage === 'siswa' ? 'active bg-white text-success fw-bold rounded' : '' ?>" title="Data Siswa">
                    <i class="fa-solid fa-users me-2"></i> <span class="sidebar-text">Data Siswa</span>
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="dashboard.php?page=riwayat" class="nav-link text-white <?= $currentPage === 'riwayat' ? 'active bg-white text-success fw-bold rounded' : '' ?>" title="Riwayat Pembayaran">
                    <i class="fa-solid fa-receipt me-2"></i> <span class="sidebar-text">Riwayat Pembayaran</span>
                </a>
            </li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item mb-1">
                <a href="dashboard.php?page=pengaturan" class="nav-link text-white <?= $currentPage === 'pengaturan' ? 'active bg-white text-success fw-bold rounded' : '' ?>" title="Pengaturan">
                    <i class="fa-solid fa-gear me-2"></i> <span class="sidebar-text">Pengaturan</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Bagian Bawah: Tombol Toggle Buka/Tutup & Tombol Logout -->
    <div class="mt-auto">
        <!-- TOMBOL TOGGLE DI DALAM SIDEBAR -->
        <button type="button" class="btn btn-outline-light w-100 mb-2 py-2 fw-semibold d-flex align-items-center justify-content-center" id="sidebarToggle" title="Buka/Tutup Sidebar">
            <i class="fa-solid fa-bars me-2 toggle-icon"></i> <span class="sidebar-text">Buka Menu</span>
        </button>

        <!-- Tombol Logout -->
        <a href="logout.php" class="nav-link text-danger bg-white fw-bold rounded text-center py-2" onclick="return confirm('Yakin ingin keluar?')" title="Logout">
            <i class="fa-solid fa-right-from-bracket me-2"></i> <span class="sidebar-text">Logout</span>
        </a>
    </div>
</nav>

<!-- Styling CSS untuk Sidebar Terlipat / Tertutup -->
<style>
    .sidebar.collapsed {
        width: 80px !important;
    }
    .sidebar.collapsed .sidebar-text,
    .sidebar.collapsed .sidebar-text-content {
        display: none !important;
    }
    .sidebar.collapsed .nav-link,
    .sidebar.collapsed #sidebarToggle {
        text-align: center;
        padding-left: 0;
        padding-right: 0;
    }
    .sidebar.collapsed #sidebarToggle i {
        margin: 0 !important;
    }
    .sidebar.collapsed .nav-link i {
        font-size: 1.25rem;
        margin: 0 !important;
    }
    .sidebar.collapsed .logo-img {
        max-width: 40px !important;
    }
</style>

<script>
    // Bungkus dengan DOMContentLoaded agar script aman dieksekusi setelah halaman siap
    document.addEventListener("DOMContentLoaded", function () {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle && sidebar) {
            // Set teks awal tombol menyesuaikan kondisi default sidebar (collapsed)
            const textSpan = sidebarToggle.querySelector('.sidebar-text');
            if (sidebar.classList.contains('collapsed')) {
                if (textSpan) textSpan.textContent = "Buka Menu";
                sidebarToggle.title = "Buka Sidebar";
            } else {
                if (textSpan) textSpan.textContent = "Tutup Menu";
                sidebarToggle.title = "Tutup Sidebar";
            }

            // Event klik untuk membuka/menutup sidebar
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                
                if (sidebar.classList.contains('collapsed')) {
                    if (textSpan) textSpan.textContent = "Buka Menu";
                    this.title = "Buka Sidebar";
                } else {
                    if (textSpan) textSpan.textContent = "Tutup Menu";
                    this.title = "Tutup Sidebar";
                }
            });
        }
    });
</script>