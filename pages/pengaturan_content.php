<?php
// Pastikan session sudah berjalan
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$success_msg = "";
$error_msg = "";

// ==========================================
// 1. PROSES GANTI PASSWORD
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $id_petugas = $_SESSION['id_petugas'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "Semua bidang password wajib diisi!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "Konfirmasi password baru tidak cocok!";
    } else {
        try {
            // Ambil password lama dari database untuk dicocokkan
            $stmt = $pdo->prepare("SELECT password FROM petugas WHERE id_petugas = ?");
            $stmt->execute([$id_petugas]);
            $user = $stmt->fetch();

            if ($user && password_verify($current_password, $user['password'])) {
                // Enkripsi password baru yang aman
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password ke database
                $update_stmt = $pdo->prepare("UPDATE petugas SET password = ? WHERE id_petugas = ?");
                $update_stmt->execute([$hashed_password, $id_petugas]);
                
                $success_msg = "Password Anda berhasil diperbarui!";
            } else {
                $error_msg = "Password saat ini (lama) yang Anda masukkan salah!";
            }
        } catch (PDOException $e) {
            $error_msg = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

// ==========================================
// 2. PROSES TAMBAH JENIS PEMBAYARAN
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_jp'])) {
    $nama_pembayaran = filter_input(INPUT_POST, 'nama_pembayaran', FILTER_SANITIZE_SPECIAL_CHARS);
    $nominal = filter_input(INPUT_POST, 'nominal', FILTER_VALIDATE_FLOAT);
    $tahun_ajaran = filter_input(INPUT_POST, 'tahun_ajaran', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($nama_pembayaran) && $nominal !== false && !empty($tahun_ajaran)) {
        try {
            // Menggunakan struktur tabel asli Anda
            $stmt = $pdo->prepare("INSERT INTO jenis_pembayaran (nama_pembayaran, nominal, tahun_ajaran) VALUES (?, ?, ?)");
            $stmt->execute([$nama_pembayaran, $nominal, $tahun_ajaran]);
            $success_msg = "Jenis pembayaran baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $error_msg = "Gagal menambah jenis pembayaran: " . $e->getMessage();
        }
    } else {
        $error_msg = "Semua kolom jenis pembayaran wajib diisi!";
    }
}

// ==========================================
// 3. PROSES HAPUS JENIS PEMBAYARAN
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'hapus_jp' && isset($_GET['id'])) {
    $id_jenis = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM jenis_pembayaran WHERE id_jenis = ?");
        $stmt->execute([$id_jenis]);
        $success_msg = "Jenis pembayaran berhasil dihapus dari sistem.";
    } catch (PDOException $e) {
        $error_msg = "Gagal menghapus data jenis pembayaran: " . $e->getMessage();
    }
}

// ==========================================
// 4. AMBIL DATA JENIS PEMBAYARAN
// ==========================================
try {
    $stmt = $pdo->query("SELECT * FROM jenis_pembayaran ORDER BY id_jenis DESC");
    $daftar_jp = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data jenis pembayaran: " . $e->getMessage());
}
?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success m-0" style="font-family: 'Poppins', sans-serif;">
            <i class="fa-solid fa-gear me-2"></i>Pengaturan Sistem
        </h3>
    </div>

    <!-- Notifikasi Sukses / Gagal -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-check me-2"></i> <?= $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius: 10px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- BARIS PERTAMA: Profil & Ganti Password -->
    <div class="row g-4 mb-4">
        <!-- Kolom Kiri: Profil Pengguna -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border-0 text-center p-4" style="border-radius: 15px; background: #ffffff;">
                <div class="card-body">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
                        <?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Nama Pengguna'); ?></h5>
                    <p class="text-muted small mb-3">@<?= htmlspecialchars($_SESSION['username'] ?? 'username'); ?></p>
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold" style="font-size: 11px; text-transform: uppercase;">
                        <i class="fa-solid fa-shield me-1"></i><?= htmlspecialchars($_SESSION['level'] ?? ($_SESSION['role'] ?? 'Petugas')); ?>
                    </span>
                    <hr class="my-4" style="border-color: #f1f3f5;">
                    <div class="text-start small text-muted">
                        <div class="mb-2"><strong>ID Petugas:</strong> <span class="float-end text-dark"><?= htmlspecialchars($_SESSION['id_petugas'] ?? '-'); ?></span></div>
                        <div><strong>Sesi Aktif Sejak:</strong> <span class="float-end text-dark"><?= date('H:i'); ?> WIB</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Keamanan (Ganti Password) -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: #ffffff;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-lock me-2 text-success"></i>Keamanan Akun (Ganti Password)</h5>
                    <p class="text-muted small m-0 mt-1">Gunakan kombinasi password yang kuat untuk menjaga keamanan akun Anda.</p>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <!-- Password Lama -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Password Saat Ini (Lama)</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Masukkan password lama Anda" required style="border-radius: 8px; font-size: 14px; padding: 10px 12px;">
                        </div>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required style="border-radius: 8px; font-size: 14px; padding: 10px 12px;">
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Ulangi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi password baru" required style="border-radius: 8px; font-size: 14px; padding: 10px 12px;">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success fw-bold px-4 py-2" style="border-radius: 8px; font-size: 14px;">
                                <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Password Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- BARIS KEDUA: Manajemen Jenis Pembayaran -->
    <div class="row g-4">
        <!-- Form Tambah Jenis Pembayaran -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: #ffffff;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-plus-circle me-2 text-success"></i>Tambah Jenis Pembayaran</h5>
                    <p class="text-muted small m-0 mt-1">Daftarkan jenis tagihan baru untuk siswa.</p>
                </div>
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <input type="hidden" name="tambah_jp" value="1">

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nama Pembayaran</label>
                            <input type="text" name="nama_pembayaran" class="form-control" placeholder="Contoh: SPP Juli 2026" required style="border-radius: 8px; font-size: 14px; padding: 10px 12px;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Nominal Tarif (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success fw-bold" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; font-size: 14px;">Rp</span>
                                <input type="number" name="nominal" class="form-control" placeholder="Contoh: 250000" required style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; font-size: 14px; padding: 10px 12px;">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 13px;">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" class="form-control" placeholder="Contoh: 2026/2027" required style="border-radius: 8px; font-size: 14px; padding: 10px 12px;">
                        </div>

                        <button type="submit" class="btn btn-success fw-bold w-100 py-2" style="border-radius: 8px; font-size: 14px;">
                            <i class="fa-solid fa-save me-2"></i>Simpan Jenis Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel List Jenis Pembayaran Aktif -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border-0" style="border-radius: 15px; background: #ffffff;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark m-0"><i class="fa-solid fa-list me-2 text-success"></i>Tarif & Jenis Pembayaran Aktif</h5>
                    <p class="text-muted small m-0 mt-1">Berikut adalah daftar tagihan yang telah tersimpan di sistem.</p>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="font-size: 14px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 8%" class="text-secondary fw-semibold">No</th>
                                    <th class="text-secondary fw-semibold">Nama Pembayaran</th>
                                    <th class="text-secondary fw-semibold">Nominal</th>
                                    <th class="text-secondary fw-semibold">Tahun Ajaran</th>
                                    <th class="text-center text-secondary fw-semibold" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($daftar_jp) > 0): ?>
                                    <?php $no = 1; foreach ($daftar_jp as $jp): ?>
                                        <tr>
                                            <td class="fw-bold text-muted"><?= $no++; ?></td>
                                            <td class="fw-bold text-success"><?= htmlspecialchars($jp['nama_pembayaran']); ?></td>
                                            <td class="fw-semibold text-dark">Rp <?= number_format($jp['nominal'], 0, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 11px;">
                                                    <?= htmlspecialchars($jp['tahun_ajaran']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="dashboard.php?page=pengaturan&action=hapus_jp&id=<?= $jp['id_jenis']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus jenis pembayaran ini?');"
                                                   style="border-radius: 6px;"
                                                   title="Hapus">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            <i class="fa-regular fa-folder-open d-block mb-2 fs-3 text-secondary"></i>
                                            Belum ada jenis pembayaran yang terdaftar.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>