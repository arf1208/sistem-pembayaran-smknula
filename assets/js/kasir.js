document.addEventListener('DOMContentLoaded', function () {
    const btnCari = document.getElementById('btn-cari');
    const inputNis = document.getElementById('input-nis');
    const formNis = document.getElementById('form-nis');
    const formJenis = document.getElementById('form-jenis');
    const formNominal = document.getElementById('form-nominal');
    const formDiskon = document.getElementById('form-diskon');
    const formDenda = document.getElementById('form-denda');
    const grandTotalElement = document.getElementById('grand-total');
    const btnSimpan = document.getElementById('btn-simpan');

    // Pencarian Siswa
    function cariSiswa() {
        const keyword = inputNis.value.trim();
        if (!keyword) {
            Swal.fire('Informasi', 'Masukkan NIS atau Nama siswa!', 'warning');
            return;
        }

        document.getElementById('search-loader').classList.remove('d-none');
        document.getElementById('biodata-siswa').classList.add('d-none');

        fetch(`api/cari_siswa.php?nis=${encodeURIComponent(keyword)}`)
            .then(response => response.json())
            .then(res => {
                document.getElementById('search-loader').classList.add('d-none');
                
                if (res.status === 'success') {
                    // Update Panel Biodata (ID disesuaikan dengan HTML Anda)
                    document.getElementById('biodata-siswa').classList.remove('d-none');
                    
                    // PERBAIKAN: Menggunakan ID 'view-nama' sesuai HTML Anda
                    document.getElementById('view-nama').innerText = res.data.nama_lengkap;
                    document.getElementById('view-kelas-jurusan').innerText = `${res.data.kelas} | ${res.data.jurusan}`;
                    
                    const fotoUrl = res.data.foto && res.data.foto !== 'default.png' ? `uploads/${res.data.foto}` : 'https://placehold.co/150x150/1f7a3e/ffffff?text=FOTO';
                    document.getElementById('view-foto').src = fotoUrl;
                    
                    formNis.value = res.data.nis;

                    // Update Riwayat Transaksi
                    const listRiwayat = document.getElementById('list-riwayat');
                    listRiwayat.innerHTML = '';
                    if (!res.data.riwayat || res.data.riwayat.length === 0) {
                        listRiwayat.innerHTML = `<tr><td colspan="3" class="text-center text-muted">Belum ada riwayat transaksi.</td></tr>`;
                    } else {
                        res.data.riwayat.forEach(tx => {
                            listRiwayat.innerHTML += `
                                <tr>
                                    <td>${tx.tanggal_bayar}</td>
                                    <td>${tx.nama_pembayaran}</td>
                                    <td class="fw-bold text-success">Rp ${parseFloat(tx.total_akhir).toLocaleString('id-ID')}</td>
                                </tr>`;
                        });
                    }

                    // Isi Dropdown Jenis Pembayaran
                    formJenis.innerHTML = '<option value="">-- Pilih Pembayaran --</option>';
                    res.jenis_pembayaran.forEach(item => {
                        formJenis.innerHTML += `<option value="${item.id_jenis}" data-nominal="${item.nominal}">${item.nama_pembayaran} (Rp ${parseFloat(item.nominal).toLocaleString('id-ID')})</option>`;
                    });
                    
                    formJenis.disabled = false;
                    btnSimpan.disabled = false;
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('search-loader').classList.add('d-none');
                Swal.fire('Error', 'Gagal memproses permintaan ke server.', 'error');
            });
    }

    // Event Listener Pencarian
    if (btnCari) btnCari.addEventListener('click', cariSiswa);
    if (inputNis) {
        inputNis.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') cariSiswa();
        });
    }

    // Kalkulasi Total Instan
    function hitungTotal() {
        const nominal = parseFloat(formNominal.value.replace(/[^0-9]/g, '')) || 0;
        const diskon = parseFloat(formDiskon.value) || 0;
        const denda = parseFloat(formDenda.value) || 0;
        const total = (nominal - diskon) + denda;
        grandTotalElement.innerText = `Rp ${total.toLocaleString('id-ID')}`;
    }

    if (formJenis) {
        formJenis.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const nominal = selectedOption.getAttribute('data-nominal') || 0;
            formNominal.value = parseFloat(nominal).toLocaleString('id-ID');
            hitungTotal();
        });
    }

    if (formDiskon) formDiskon.addEventListener('input', hitungTotal);
    if (formDenda) formDenda.addEventListener('input', hitungTotal);

    // Proses Submit Pembayaran (AJAX)
    const formPembayaran = document.getElementById('form-pembayaran');
    if (formPembayaran) {
        formPembayaran.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Pembayaran?',
                text: "Pastikan nominal sudah sesuai!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1F7A3E',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('api/simpan_transaksi.php', {
                        method: 'POST',
                        body: new FormData(this)
                    })
                    .then(res => res.json())
                    .then(res => {
                        if(res.status === 'success') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Cetak struk sekarang?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Cetak',
                                cancelButtonText: 'Tutup'
                            }).then((printResult) => {
                                if (printResult.isConfirmed) {
                                    window.open(`receipt/cetak_thermal.php?no_transaksi=${res.no_transaksi}`, '_blank');
                                }
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    });
                }
            });
        });
    }
});