<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo '<div class="alert alert-danger m-4">NIS siswa tidak ditemukan.</div>';
    exit();
}

// Ambil data siswa berdasarkan NIS
try {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nis = ?");
    $stmt->execute([$id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa) {
        echo '<div class="alert alert-danger m-4">Data siswa tidak ditemukan di database.</div>';
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$pesan_error = '';

// Proses update data ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_siswa'])) {
    $nisn = filter_input(INPUT_POST, 'nisn', FILTER_SANITIZE_SPECIAL_CHARS);
    $nama_lengkap = filter_input(INPUT_POST, 'nama_lengkap', FILTER_SANITIZE_SPECIAL_CHARS);
    $jk = filter_input(INPUT_POST, 'jk', FILTER_SANITIZE_SPECIAL_CHARS);
    $kelas = filter_input(INPUT_POST, 'kelas', FILTER_SANITIZE_SPECIAL_CHARS);
    $jurusan = filter_input(INPUT_POST, 'jurusan', FILTER_SANITIZE_SPECIAL_CHARS);
    $hp_ortu = filter_input(INPUT_POST, 'hp_ortu', FILTER_SANITIZE_SPECIAL_CHARS);
    $alamat = filter_input(INPUT_POST, 'alamat', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($nama_lengkap) && !empty($kelas) && !empty($jurusan)) {
        try {
            $update = $pdo->prepare("UPDATE siswa SET nisn = ?, nama_lengkap = ?, jk = ?, kelas = ?, jurusan = ?, hp_ortu = ?, alamat = ? WHERE nis = ?");
            $update->execute([$nisn, $nama_lengkap, $jk, $kelas, $jurusan, $hp_ortu, $alamat, $id]);

            // KODE BARU (Aman dari error header)
            echo "<script>window.location.href='dashboard.php?page=siswa';</script>";
            exit();
        } catch (PDOException $e) {
            $pesan_error = "Gagal memperbarui data: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Harap isi kolom wajib (*).";
    }
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success m-0"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Data Siswa</h3>
        <a href="dashboard.php?page=siswa" class="btn btn-secondary btn-sm fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <?php if ($pesan_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $pesan_error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body p-4">
            <form action="" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">NIS (Tidak dapat diubah)</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($siswa['nis']); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">NISN</label>
                        <input type="text" name="nisn" class="form-control" value="<?= htmlspecialchars($siswa['nisn'] ?? ''); ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($siswa['nama_lengkap'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jk" class="form-select" required>
                            <option value="L" <?= ($siswa['jk'] ?? '') === 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?= ($siswa['jk'] ?? '') === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Kelas <span class="text-danger">*</span></label>
                        <select name="kelas" class="form-select" required>
                            <option value="X" <?= ($siswa['kelas'] ?? '') === 'X' ? 'selected' : ''; ?>>X</option>
                            <option value="XI" <?= ($siswa['kelas'] ?? '') === 'XI' ? 'selected' : ''; ?>>XI</option>
                            <option value="XII" <?= ($siswa['kelas'] ?? '') === 'XII' ? 'selected' : ''; ?>>XII</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Jurusan <span class="text-danger">*</span></label>
                        <select name="jurusan" class="form-select" required>
                            <?php $jurusan_list = ['TSM', 'TKJ', 'AK', 'OTKP', 'DKV']; ?>
                            <?php foreach ($jurusan_list as $j): ?>
                                <option value="<?= $j; ?>" <?= ($siswa['jurusan'] ?? '') === $j ? 'selected' : ''; ?>><?= $j; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">No. HP Ortu</label>
                        <input type="text" name="hp_ortu" class="form-control" value="<?= htmlspecialchars($siswa['hp_ortu'] ?? ''); ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold small">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="mt-4 pt-2 border-top text-end">
                    <a href="dashboard.php?page=siswa" class="btn btn-secondary fw-semibold me-2">Batal</a>
                    <button type="submit" name="update_siswa" class="btn btn-success fw-bold px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>