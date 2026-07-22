<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

$id = $_GET['id'] ?? '';
$aksi = $_GET['aksi'] ?? '';

if (!empty($id)) {
    // 1. OPSI MERAH: Hapus Permanen Siswa BESERTA Seluruh Riwayat Transaksinya
    if ($aksi === 'hapus_semua') {
        try {
            $pdo->beginTransaction();

            $stmt_transaksi = $pdo->prepare("DELETE FROM transaksi WHERE nis = ?");
            $stmt_transaksi->execute([$id]);

            $stmt_siswa = $pdo->prepare("DELETE FROM siswa WHERE nis = ?");
            $stmt_siswa->execute([$id]);

            $pdo->commit();

            header("Location: dashboard.php?page=siswa&status=sukses_hapus");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo '<div class="alert alert-danger m-4">Gagal menghapus data secara menyeluruh: ' . $e->getMessage() . '</div>';
        }
    }

    // 2. OPSI KUNING: Hapus Data Siswanya Saja (Riwayat Transaksi Aman)
    // Syarat: Pastikan tabel transaksi kamu kolom foreign key-nya terset ON DELETE SET NULL atau ON DELETE CASCADE. 
    // Jika tidak mendukung, kita gunakan trik set NULL/bypass dulu.
    if ($aksi === 'hapus_siswa_saja') {
        try {
            // Jika relasi database mewajibkan, kita putuskan relasi transaksi ke siswa ini dulu (ubah nis jadi NULL atau hapus kaitan jika kolom mengizinkan)
            // Atau jika foreign key mengizinkan restrict, kita tangani lewat update atau langsung hapus siswa jika settingan database membolehkan.
            // Alternatif aman: Coba hapus siswanya langsung.
            $stmt_siswa = $pdo->prepare("DELETE FROM siswa WHERE nis = ?");
            $stmt_siswa->execute([$id]);

            header("Location: dashboard.php?page=siswa&status=sukses_hapus");
            exit();
        } catch (PDOException $e) {
            // Fallback jika database masih menolak karena constraint, kita beri opsi ubah relasi atau tampilkan pesan
            echo '
            <div class="container-fluid px-4 py-4">
                <div class="alert alert-warning shadow-sm border-0 p-4" style="border-radius: 15px;">
                    <h4 class="fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Catatan Database</h4>
                    <p class="mb-3">Database Anda saat ini mengunci relasi tabel (Foreign Key Strict). Agar data siswa bisa dihapus tanpa menghapus transaksinya, struktur kolom <code>nis</code> pada tabel <code>transaksi</code> harus diset <code>ON DELETE SET NULL</code> atau relasi constraint-nya dilepas terlebih dahulu via phpMyAdmin.</p>
                    <a href="dashboard.php?page=siswa" class="btn btn-secondary fw-semibold">Kembali ke Data Siswa</a>
                </div>
            </div>';
            exit();
        }
    }

    try {
        // Percobaan hapus normal (jika siswa tidak punya transaksi)
        $stmt = $pdo->prepare("DELETE FROM siswa WHERE nis = ?");
        $stmt->execute([$id]);

        header("Location: dashboard.php?page=siswa&status=sukses_hapus");
        exit();
    } catch (PDOException $e) {
        // Jika terbentur foreign key (kode 1451), tampilkan 3 opsi tombol (Merah, Kuning, Hijau)
        if ($e->getCode() == '23000') {
            echo '
            <div class="container-fluid px-4 py-4">
                <div class="alert alert-danger shadow-sm border-0 p-4" style="border-radius: 15px; background-color: #fff3f3; border-left: 6px solid #dc3545 !important;">
                    <h4 class="fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i>Siswa Memiliki Riwayat Transaksi</h4>
                    <p class="mb-3 text-dark">Siswa dengan NIS <b>' . htmlspecialchars($id) . '</b> masih tercatat memiliki data pembayaran di sistem.</p>
                    <hr>
                    <p class="fw-semibold mb-3 text-secondary">Silakan pilih tindakan yang ingin Anda lakukan:</p>
                    
                    <div class="d-flex flex-wrap gap-2">
                        <!-- OPSI 1: MERAH (Hapus Permanen Keduanya) -->
                        <a href="dashboard.php?page=hapus_siswa&id=' . urlencode($id) . '&aksi=hapus_semua" class="btn btn-danger fw-bold px-3 py-2" onclick="return confirm(\'PERINGATAN KERAS: Data siswa BESERTA seluruh riwayat transaksinya akan terhapus permanen! Lanjutkan?\')">
                            <i class="fa-solid fa-trash-can me-1"></i> Hapus Keduanya (Permanen)
                        </a>

                        <!-- OPSI 2: KUNING (Hapus Data Siswa Saja, Transaksi Aman) -->
                        <a href="dashboard.php?page=hapus_siswa&id=' . urlencode($id) . '&aksi=hapus_siswa_saja" class="btn btn-warning text-dark fw-bold px-3 py-2" onclick="return confirm(\'Data siswanya akan dihapus, namun riwayat transaksinya tetap aman tersimpan. Lanjutkan?\')">
                            <i class="fa-solid fa-user-xmark me-1"></i> Hapus Data Siswa Saja (Transaksi Aman)
                        </a>

                        <!-- OPSI 3: HIJAU (Tombol Kembali) -->
                        <a href="dashboard.php?page=siswa" class="btn btn-success fw-bold px-3 py-2">
                            <i class="fa-solid fa-arrow-left me-1"></i> Kembali / Batal
                        </a>
                    </div>
                </div>
            </div>';
        } else {
            echo '<div class="alert alert-danger m-4">Gagal menghapus data siswa: ' . $e->getMessage() . '</div>';
        }
    }
} else {
    echo '<div class="alert alert-warning m-4">NIS siswa tidak valid.</div>';
}
?>