<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

// 1. PROSES TAMBAH SISWA
$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_siswa'])) {
    $nis          = trim($_POST['nis'] ?? '');
    $nisn         = trim($_POST['nisn'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $jk           = trim($_POST['jk'] ?? '');
    $kelas        = trim($_POST['kelas'] ?? '');
    $jurusan      = trim($_POST['jurusan'] ?? '');
    $hp_ortu      = trim($_POST['hp_ortu'] ?? '');
    $alamat       = trim($_POST['alamat'] ?? '');

    if (!empty($nis) && !empty($nama_lengkap) && !empty($jk) && !empty($kelas) && !empty($jurusan)) {
        try {
            $stmt_insert = $pdo->prepare("INSERT INTO siswa (nis, nisn, nama_lengkap, jk, kelas, jurusan, hp_ortu, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert->execute([$nis, $nisn, $nama_lengkap, $jk, $kelas, $jurusan, $hp_ortu, $alamat]);
            
            // Redirect dengan JavaScript agar bersih dan aman dari error header
            echo "<script>window.location.href='dashboard.php?page=siswa';</script>";
            exit();
        } catch (PDOException $e) {
            $pesan_error = "Gagal menyimpan: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua kolom bertanda bintang wajib diisi!";
    }
}

// 2. AMBIL DATA SISWA DENGAN FITUR PENCARIAN
$keyword_cari = trim($_GET['cari'] ?? '');

try {
    if (!empty($keyword_cari)) {
        $stmt = $pdo->prepare("SELECT * FROM siswa WHERE nis LIKE ? OR nama_lengkap LIKE ? ORDER BY kelas ASC, nama_lengkap ASC");
        $stmt->execute(["%$keyword_cari%", "%$keyword_cari%"]);
    } else {
        $stmt = $pdo->query("SELECT * FROM siswa ORDER BY kelas ASC, nama_lengkap ASC");
    }
    $daftar_siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $daftar_siswa = [];
    $db_error = $e->getMessage();
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-success m-0"><i class="fa-solid fa-graduation-cap me-2"></i>Data Siswa</h3>
        <button class="btn btn-success btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
            <i class="fa-solid fa-plus me-1"></i> Tambah Siswa
        </button>
    </div>

    <?php if (!empty($pesan_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($pesan_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger">Error Database: <?= htmlspecialchars($db_error); ?></div>
    <?php endif; ?>

    <!-- CARD TABEL & FORM PENCARIAN + FILTER KELAS -->
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header bg-white py-3">
            <div class="row g-2 align-items-center justify-content-between">
                <!-- Form Pencarian Bawaan -->
                <div class="col-md-5">
                    <form action="" method="GET" class="input-group">
                        <input type="hidden" name="page" value="siswa">
                        <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari NIS atau Nama Siswa..." value="<?= htmlspecialchars($keyword_cari); ?>">
                        <button type="submit" class="btn btn-success btn-sm fw-bold px-3">
                            <i class="fa-solid fa-search"></i> Cari
                        </button>
                        <?php if (!empty($keyword_cari)): ?>
                            <a href="dashboard.php?page=siswa" class="btn btn-outline-secondary btn-sm" title="Reset Pencarian">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Dropdown Filter Kelas (JavaScript Real-time) -->
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <label class="input-group-text bg-success text-white fw-semibold" for="filter-kelas">
                            <i class="fa-solid fa-filter me-1"></i> Kelas
                        </label>
                        <select id="filter-kelas" class="form-select form-select-sm">
                            <option value="">Semua Kelas</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center small" id="tabel-siswa">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2.5" style="width: 4%;">No</th>
                            <th class="py-2.5" style="width: 10%;">NIS</th>
                            <th class="py-2.5" style="width: 10%;">NISN</th>
                            <th class="py-2.5" style="width: 18%;">Nama Lengkap</th>
                            <th class="py-2.5" style="width: 5%;">JK</th>
                            <th class="py-2.5" style="width: 6%;">Kelas</th>
                            <th class="py-2.5" style="width: 12%;">Jurusan</th>
                            <th class="py-2.5" style="width: 12%;">No. HP Ortu</th>
                            <th class="py-2.5" style="width: 13%;">Alamat</th>
                            <th class="py-2.5" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_siswa)): ?>
                            <?php $no = 1; foreach ($daftar_siswa as $siswa): ?>
                                <?php 
                                    $id_key = isset($siswa['nis']) ? $siswa['nis'] : '';
                                ?>
                                <!-- Tambahkan atribut data-kelas agar bisa dibaca oleh JavaScript -->
                                <tr data-kelas="<?= htmlspecialchars($siswa['kelas'] ?? ''); ?>">
                                    <td class="fw-bold text-muted"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($siswa['nis'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($siswa['nisn'] ?? ''); ?></td>
                                    <td class="fw-bold text-success text-start"><?= htmlspecialchars($siswa['nama_lengkap'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($siswa['jk'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($siswa['kelas'] ?? ''); ?></span>
                                    </td>
                                    <td class="text-start"><?= htmlspecialchars($siswa['jurusan'] ?? ''); ?></td>
                                    <td>
                                        <?php if (!empty($siswa['hp_ortu'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                <i class="fa-brands fa-whatsapp me-1"></i><?= htmlspecialchars($siswa['hp_ortu']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-start"><?= htmlspecialchars($siswa['alamat'] ?? ''); ?></td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="dashboard.php?page=edit_siswa&id=<?= $id_key; ?>" class="btn btn-sm btn-warning text-dark fw-bold px-2 py-1" style="font-size: 0.75rem;" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </a>
                                            <a href="dashboard.php?page=hapus_siswa&id=<?= $id_key; ?>" class="btn btn-sm btn-danger fw-bold px-2 py-1" style="font-size: 0.75rem;" onclick="return confirm('Yakin ingin menghapus data ini?')" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">Data siswa tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL BOX TAMBAH SISWA -->
<div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Form Tambah Siswa Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">NISN</label>
                            <input type="text" name="nisn" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jk" class="form-select" required>
                                <option value="" disabled selected>Pilih</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas" class="form-select" required>
                                <option value="" disabled selected>Pilih</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Jurusan <span class="text-danger">*</span></label>
                            <select name="jurusan" class="form-select" required>
                                <option value="" disabled selected>Pilih</option>
                                <option value="TSM">TSM</option>
                                <option value="TKJ">TKJ</option>
                                <option value="AK">AK</option>
                                <option value="OTKP">OTKP</option>
                                <option value="DKV">DKV</option>
                                <option value="Farmasi">Farmasi</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">No. HP Ortu <span class="text-danger">*</span></label>
                            <input type="text" name="hp_ortu" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_siswa" class="btn btn-success fw-bold px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript untuk Filter Kelas Real-time -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterKelas = document.getElementById('filter-kelas');
    const trSiswa = document.querySelectorAll('#tabel-siswa tbody tr');

    if (filterKelas) {
        filterKelas.addEventListener('change', function() {
            const kelasPilih = this.value.toLowerCase();
            let adaData = false;

            trSiswa.forEach(tr => {
                // Abaikan baris kosong ("Data siswa tidak ditemukan.")
                if (tr.cells.length === 1) return;

                const kelasSiswa = (tr.getAttribute('data-kelas') || '').toLowerCase();

                if (kelasPilih === "" || kelasSiswa === kelasPilih) {
                    tr.style.display = "";
                    adaData = true;
                } else {
                    tr.style.display = "none";
                }
            });
        });
    }
});
</script>