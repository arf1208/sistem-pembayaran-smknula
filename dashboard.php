<?php
// 1. Memuat konfigurasi database dan session global
require_once 'config/database.php';

// 2. Proteksi Halaman: Jika belum login, tendang ke login.php
if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

// 3. Memuat komponen layout atas (HTML, CSS, Library CDN)
include 'includes/header.php';
?>

<!-- WRAPPER UTAMA FLEXBOX -->
<div class="d-flex" id="wrapper">

    <?php 
    // 4. Memuat komponen menu navigasi samping (Sidebar)
    include 'includes/sidebar.php'; 
    ?>

    <!-- KONTEN UTAMA YANG BERUBAH MENJADI FLEKSIBEL -->
    <div class="flex-grow-1 bg-light main-content-wrapper" style="min-height: 100vh;">
        
        <!-- TOP BAR ELEGAN & REAL-TIME CLOCK (Tombol garis tiga sudah dihapus dari sini) -->
        <div class="d-flex justify-content-between align-items-center mb-4 card-custom p-3 border-gold m-4" data-aos="fade-down" style="background: #ffffff; border-radius: 12px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
            <div>
                <h5 class="m-0 fw-bold text-success">Sistem Pembayaran SMK NU Lamongan</h5>
                <small id="clock-display" class="text-muted"></small>
            </div>
            <div>
                <span class="badge bg-success p-2 fs-6 shadow-sm">
                    <i class="fa-solid fa-user-shield me-2"></i><?= htmlentities($_SESSION['nama_lengkap']) ?> (<?= strtoupper($_SESSION['role']) ?>)
                </span>
            </div>
        </div>

        <!-- DINAMIS ROUTER UNTUK KONTEN HALAMAN -->
        <div class="container-fluid px-4 pb-4">
            <?php 
                // Mengambil parameter halaman dari URL (?page=...)
                $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'dashboard';
                
                // Proteksi jalur file (Mencegah Directory Traversal)
                $allowed_pages = ['dashboard', 'kasir', 'siswa', 'riwayat', 'pengaturan', 'edit_siswa', 'hapus_siswa'];
                
                if (in_array($page, $allowed_pages)) {
                    $file_path = "pages/" . $page . "_content.php";
                    
                    // Khusus halaman aksi satuan jika file-nya dinamai langsung tanpa '_content'
                    if (!file_exists($file_path)) {
                        $file_path = "pages/" . $page . ".php";
                    }

                    if (file_exists($file_path)) {
                        include $file_path;
                    } else {
                        echo '<div class="alert alert-danger shadow-sm border-0"><i class="fa-solid fa-circle-exclamation me-2"></i>File halaman <b>' . $page . '</b> belum dibuat di dalam folder pages/.</div>';
                    }
                } else {
                    echo '<div class="alert alert-warning shadow-sm border-0"><i class="fa-solid fa-ban me-2"></i>Halaman tidak ditemukan atau Anda tidak memiliki hak akses.</div>';
                }
            ?>
        </div>
    </div>
</div>

<!-- REAL-TIME CLOCK, AUTO LOGOUT SYNC, & SIDEBAR TOGGLE -->
<script>
    function updateClock() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const format = days[now.getDay()] + ', ' +
                       String(now.getDate()).padStart(2, '0') + '-' +
                       String(now.getMonth() + 1).padStart(2, '0') + '-' +
                       now.getFullYear() + ' | ' +
                       String(now.getHours()).padStart(2, '0') + ':' +
                       String(now.getMinutes()).padStart(2, '0') + ':' +
                       String(now.getSeconds()).padStart(2, '0') + ' WIB';
        const clockEl = document.getElementById('clock-display');
        if (clockEl) clockEl.innerText = format;
    }
    setInterval(updateClock, 1000);
    window.addEventListener('DOMContentLoaded', updateClock);

    // Script Toggle Buka / Tutup Sidebar
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
            });
        }
    });
</script>

<?php
// 5. Memuat komponen layout bawah (Script JavaScript, SweetAlert, ChartJS)
include 'includes/footer.php';
?>