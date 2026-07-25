<div class="row">
    <!-- PANEL CARI & BIODATA SISWA -->
    <div class="col-lg-5 mb-4" data-aos="fade-right">
        <div class="card card-custom p-4 border-gold">
            <h5 class="fw-bold text-success mb-3"><i class="fa-solid fa-user-check me-2"></i>Cari & Verifikasi Siswa</h5>
            <div class="input-group mb-3">
                <input type="text" id="input-nis" class="form-control" placeholder="Masukkan NIS atau Nama..." autofocus>
                <button class="btn btn-success" type="button" id="btn-cari"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            </div>

            <!-- Loader Pencarian -->
            <div id="search-loader" class="text-center d-none my-3">
                <div class="spinner-border text-success" role="status"></div>
                <p class="small text-muted mt-2">Mencari data siswa...</p>
            </div>

            <!-- Tampilan Data Hasil Pencarian -->
            <div id="biodata-siswa" class="text-center d-none pt-3">
                <img id="view-foto" src="" class="rounded-circle img-thumbnail mb-3 shadow-sm" style="width: 110px; height: 110px; object-fit: cover;">
                <h5 id="view-nama" class="fw-bold text-dark mb-1"></h5>
                <p id="view-kelas-jurusan" class="text-muted small mb-3"></p>
                <span class="badge bg-light text-success border border-success px-3 py-2 mb-4">Aktif | TA 2026/2027</span>
                
                <hr>
                <h6 class="text-start fw-bold text-success mb-3"><i class="fa-solid fa-history me-2"></i>Riwayat Transaksi Terbaru</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover text-start small">
                        <thead>
                            <tr class="table-success">
                                <th>Tanggal</th>
                                <th>Pembayaran</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="list-riwayat">
                            <!-- Diisi via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL FORM TRANSAKSI -->
    <div class="col-lg-7 mb-4" data-aos="fade-left">
        <div class="card card-custom p-4">
            <h5 class="fw-bold text-success mb-4"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Form Pembayaran Baru</h5>
            
            <form id="form-pembayaran">
                <input type="hidden" name="nis" id="form-nis">
                <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Pilih Jenis Tagihan/Pembayaran</label>
                    <select class="form-select" id="form-jenis" name="id_jenis" required disabled>
                        <option value="">-- Cari siswa terlebih dahulu --</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Nominal Bayar / Cicilan (Rp)</label>
                        <input type="number" class="form-control" id="form-nominal" name="nominal_bayar" placeholder="Masukkan jumlah bayar..." required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Metode Pembayaran</label>
                        <select class="form-select" name="metode_pembayaran" required>
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                            <option value="QRIS">QRIS</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Diskon / Beasiswa (Rp)</label>
                        <input type="number" class="form-control" id="form-diskon" name="diskon" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Denda (Rp)</label>
                        <input type="number" class="form-control" id="form-denda" name="denda" value="0">
                    </div>
                </div>

                <!-- STATUS PEMBAYARAN (LUNAS / CICIL) -->
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Status Pembayaran <span class="text-danger">*</span></label>
                    <select class="form-select" id="form-status-bayar" name="status_bayar" required>
                        <option value="Lunas">Lunas</option>
                        <option value="Cicil">Cicil (Sebagian)</option>
                    </select>
                </div>

                <!-- KET/CATATAN KHUSUS CICILAN -->
                <div class="mb-3 d-none" id="wrapper-catatan">
                    <label class="form-label fw-bold small text-danger">Catatan Cicilan <span class="text-danger">*</span></label>
                    <textarea class="form-control border-danger" id="form-catatan" name="catatan" rows="2" placeholder="Contoh: Baru dibayar setengahnya, sisa tagihan..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">Keterangan / Catatan Umum</label>
                    <textarea class="form-control" name="keterangan" rows="2" placeholder="Catatan transaksi (opsional)..."></textarea>
                </div>

                <div class="p-3 bg-light rounded mb-4 border-start border-success border-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-secondary">TOTAL AKHIR :</span>
                        <h3 class="fw-bold text-success m-0" id="grand-total">Rp 0</h3>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success py-3 fw-bold shadow-sm" id="btn-simpan" disabled>
                        <i class="fa-solid fa-save me-2"></i>SIMPAN & CETAK STRUK
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/kasir.js"></script>