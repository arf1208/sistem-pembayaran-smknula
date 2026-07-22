</div> <!-- Penutup div content/container utama dari dashboard -->
</div> <!-- Penutup div row utama dari dashboard -->

<!-- Footer Dokumen -->
<footer class="footer mt-auto py-3 bg-white border-top" style="border-radius: 15px 15px 0 0; box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.025);">
    <div class="container-fluid text-center px-4">
        <!-- Baris 1: Hak Cipta & Deskripsi -->
        <div class="text-muted small mb-2">
            <span>&copy; <?= date('Y'); ?> </span>
            <span class="fw-bold text-success">SMK NU Lamongan</span>. 
            <span>Dikembangkan untuk Kemudahan Layanan Administrasi.</span>
        </div>
        
        <!-- Baris 2: Pembuat & Versi (Dibuat sejajar di tengah) -->
        <div class="text-muted small d-flex align-items-center justify-content-center gap-2 flex-wrap">
            <span>
                Made with <i class="fa-solid fa-heart text-danger"></i> by <strong class="text-dark">Siswa SMKNULA</strong>
            </span>
            <span class="text-muted">&bull;</span>
            <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1" style="font-size: 10px; border: 1px solid rgba(25, 135, 84, 0.2);">
                <i class="fa-solid fa-code-branch me-1"></i>v1.0.0
            </span>
        </div>
    </div>
</footer>

<!-- JS Core Component -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Inisialisasi AOS (Animate On Scroll) jika digunakan di halaman
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof AOS !== 'undefined') {
            AOS.init({ 
                duration: 800,
                once: true // Animasi hanya berjalan sekali saat di-scroll
            });
        }
    });
</script>
</body>
</html>