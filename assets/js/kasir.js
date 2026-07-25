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

    const selectStatusBayar = document.getElementById('form-status-bayar');
    const wrapperCatatan = document.getElementById('wrapper-catatan');
    const inputCatatan = document.getElementById('form-catatan');

    // Inisialisasi Select2 pada dropdown jenis tagihan agar ada kolom pencarian & tetap bisa di-scroll
    if (window.jQuery && $(formJenis).length) {
        $(formJenis).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih Pembayaran --',
            allowClear: true
        });

        // Hubungkan event change Select2 dengan logika aplikasi
        $(formJenis).on('change', function () {
            prosesPerubahanJenis($(this).val());
        });
    }

    // 1. Logika Jika Status Pembayaran di Bawah Diubah Manual
    if (selectStatusBayar) {
        selectStatusBayar.addEventListener('change', function() {
            if (this.value === 'Cicil') {
                wrapperCatatan.classList.remove('d-none');
                inputCatatan.setAttribute('required', 'required');

                const selectedOption = formJenis.options[formJenis.selectedIndex];
                const tipe = selectedOption ? selectedOption.getAttribute('data-tipe') : '';
                const sisa = selectedOption ? (parseFloat(selectedOption.getAttribute('data-sisa')) || 0) : 0;

                if (tipe === 'cicil' && sisa > 0) {
                    formNominal.value = sisa;
                } else {
                    formNominal.value = '';
                }
                formNominal.placeholder = 'Ketik nominal cicilan di sini...';
                formNominal.removeAttribute('readonly');
                formNominal.classList.remove('bg-light');
            } else {
                wrapperCatatan.classList.add('d-none');
                inputCatatan.removeAttribute('required');
                inputCatatan.value = '';

                const selectedOption = formJenis.options[formJenis.selectedIndex];
                const nominal = selectedOption ? (parseFloat(selectedOption.getAttribute('data-nominal')) || 0) : 0;
                formNominal.value = nominal > 0 ? nominal : '';
                formNominal.placeholder = '';
                formNominal.setAttribute('readonly', 'readonly');
                formNominal.classList.add('bg-light');
            }
            hitungTotal();
        });
    }

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
                    document.getElementById('biodata-siswa').classList.remove('d-none');
                    document.getElementById('view-nama').innerText = res.data.nama_lengkap;
                    document.getElementById('view-kelas-jurusan').innerText = `${res.data.kelas} | ${res.data.jurusan}`;
                    
                    const fotoUrl = res.data.foto && res.data.foto !== 'default.png' ? `uploads/${res.data.foto}` : 'https://placehold.co/150x150/1f7a3e/ffffff?text=FOTO';
                    document.getElementById('view-foto').src = fotoUrl;
                    formNis.value = res.data.nis;

                    // Riwayat Transaksi
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

                    // Isi Dropdown Jenis Pembayaran (Handle Select2 dengan benar)
                    if (window.jQuery && $(formJenis).data('select2')) {
                        $(formJenis).empty().trigger('change');
                    } else {
                        formJenis.innerHTML = '';
                    }

                    if (!res.jenis_pembayaran || res.jenis_pembayaran.length === 0) {
                        if (window.jQuery && $(formJenis).data('select2')) {
                            const newOption = new Option('-- Semua Pembayaran Sudah Lunas --', '', false, false);
                            $(formJenis).append(newOption).trigger('change');
                        } else {
                            formJenis.innerHTML = '<option value="">-- Semua Pembayaran Sudah Lunas --</option>';
                        }
                        formJenis.disabled = true;
                        btnSimpan.disabled = true;
                        formNominal.value = '';
                        hitungTotal();
                        Swal.fire('Informasi', 'Siswa ini sudah melunasi semua jenis pembayaran!', 'success');
                    } else {
                        if (window.jQuery && $(formJenis).data('select2')) {
                            $(formJenis).append(new Option('-- Pilih Pembayaran --', '', false, false));
                        } else {
                            formJenis.innerHTML = '<option value="">-- Pilih Pembayaran --</option>';
                        }

                        res.jenis_pembayaran.forEach(item => {
                            const nominal = parseFloat(item.nominal) || 0;
                            const sisaTagihan = item.sisa_tagihan !== undefined ? parseFloat(item.sisa_tagihan) : nominal;
                            
                            // Option Lunas
                            const labelLunas = `${item.nama_pembayaran} (Rp ${nominal.toLocaleString('id-ID')}) - Lunas`;
                            if (window.jQuery && $(formJenis).data('select2')) {
                                let opt1 = new Option(labelLunas, item.id_jenis, false, false);
                                $(opt1).attr('data-nominal', nominal);
                                $(opt1).attr('data-sisa', sisaTagihan);
                                $(opt1).attr('data-tipe', 'lunas');
                                $(formJenis).append(opt1);
                            } else {
                                formJenis.innerHTML += `<option value="${item.id_jenis}" data-nominal="${nominal}" data-sisa="${sisaTagihan}" data-tipe="lunas">${labelLunas}</option>`;
                            }
                            
                            // Option Cicil
                            let labelCicil = `${item.nama_pembayaran} - Nyicil / Sebagian`;
                            if (sisaTagihan < nominal) {
                                labelCicil = `${item.nama_pembayaran} (Sisa: Rp ${sisaTagihan.toLocaleString('id-ID')}) - Nyicil`;
                            }
                            
                            if (window.jQuery && $(formJenis).data('select2')) {
                                let opt2 = new Option(labelCicil, item.id_jenis, false, false);
                                $(opt2).attr('data-nominal', nominal);
                                $(opt2).attr('data-sisa', sisaTagihan);
                                $(opt2).attr('data-tipe', 'cicil');
                                $(formJenis).append(opt2);
                            } else {
                                formJenis.innerHTML += `<option value="${item.id_jenis}" data-nominal="${nominal}" data-sisa="${sisaTagihan}" data-tipe="cicil">${labelCicil}</option>`;
                            }
                        });

                        formJenis.disabled = false;
                        btnSimpan.disabled = false;
                    }

                    // Refresh tampilan Select2 setelah isi option diperbarui
                    if (window.jQuery && $(formJenis).length) {
                        $(formJenis).trigger('change.select2');
                    }
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

    if (btnCari) btnCari.addEventListener('click', cariSiswa);
    if (inputNis) {
        inputNis.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') cariSiswa();
        });
    }

    function hitungTotal() {
        const nominal = parseFloat(formNominal.value.replace(/[^0-9]/g, '')) || 0;
        const diskon = parseFloat(formDiskon.value) || 0;
        const denda = parseFloat(formDenda.value) || 0;
        const total = (nominal - diskon) + denda;
        grandTotalElement.innerText = `Rp ${total.toLocaleString('id-ID')}`;
    }

    // 2. Logika Pemrosesan Perubahan Jenis Pembayaran
    function prosesPerubahanJenis(idJenis) {
        const selectedOption = formJenis.options[formJenis.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            formNominal.value = '';
            hitungTotal();
            return;
        }

        const nominal = parseFloat(selectedOption.getAttribute('data-nominal')) || 0;
        const sisa = parseFloat(selectedOption.getAttribute('data-sisa')) || nominal;
        const tipe = selectedOption.getAttribute('data-tipe');

        if (tipe === 'cicil') {
            if (selectStatusBayar) selectStatusBayar.value = 'Cicil';
            wrapperCatatan.classList.remove('d-none');
            inputCatatan.setAttribute('required', 'required');

            formNominal.value = sisa > 0 ? sisa : '';
            formNominal.placeholder = 'Ketik nominal cicilan...';
            formNominal.removeAttribute('readonly');
            formNominal.classList.remove('bg-light');
        } else {
            if (selectStatusBayar) selectStatusBayar.value = 'Lunas';
            wrapperCatatan.classList.add('d-none');
            inputCatatan.removeAttribute('required');
            inputCatatan.value = '';

            formNominal.value = nominal > 0 ? nominal : '';
            formNominal.placeholder = '';
            formNominal.setAttribute('readonly', 'readonly');
            formNominal.classList.add('bg-light');
        }
        
        hitungTotal();
    }

    // Fallback jika event change biasa terpanggil
    if (formJenis && !window.jQuery) {
        formJenis.addEventListener('change', function () {
            prosesPerubahanJenis(this.value);
        });
    }

    if (formNominal) formNominal.addEventListener('input', hitungTotal);
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