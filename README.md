# 🏫 Sistem Pembayaran SMK NU Lamongan (SMKNULA)

Sistem Informasi Kasir & Administrasi Pembayaran Siswa berbasis web yang dirancang khusus untuk petugas Tata Usaha (TU) SMK NU Lamongan. Aplikasi ini mengusung desain premium, minimalis, modern, dan responsif dengan nuansa khas Nahdlatul Ulama (NU).

---

## 🚀 Fitur Utama
*   **Autentikasi Aman:** Password Hashing (`password_hash()`), proteksi CSRF token, Session Guard, dan Auto-Logout otomatis (15 menit tidak aktif).
*   **Kasir Interaktif Berbasis AJAX:** Pencarian biodata siswa, riwayat pembayaran, serta kalkulasi total tagihan secara instan tanpa perlu memuat ulang halaman (*zero-refresh*).
*   **Cetak Struk Thermal Native:** Mendukung cetak otomatis ukuran 58mm & 80mm untuk printer thermal USB (Epson, Xprinter, Rongta, POS58).
*   **Dashboard Finansial:** Ringkasan pendapatan harian, bulanan, tahunan, serta visualisasi grafik batang menggunakan *Chart.js*.
*   **Ekspor Data Multi-Format:** Fitur ekspor laporan terintegrasi ke format PDF (DomPDF), Excel (PhpSpreadsheet), dan Word (PHPWord).

---

## 🛠️ Kebutuhan Perangkat Lunak (System Requirements)
*   **Web Server & PHP:** XAMPP / Laragon (Direkomendasikan PHP versi 8.0 atau yang lebih baru).
*   **Database:** MariaDB / MySQL (Dikelola dengan HeidiSQL / phpMyAdmin).
*   **Dependency Manager:** Composer (Diperlukan untuk memasang library ekspor data).

---

## 📦 Langkah-Langkah Instalasi

1.  **Pindahkan Folder Proyek:**
    Pastikan folder utama proyek bernama `sistem_pembayaran_smknula` dan letakkan di dalam direktori web server lokal Anda:
    *   **XAMPP:** `C:/xampp/htdocs/sistem_pembayaran_smknula/`
    *   **Laragon:** `C:/laragon/www/sistem_pembayaran_smknula/`

2.  **Import Database via HeidiSQL:**
    *   Buka aplikasi **HeidiSQL** dan buat koneksi ke MySQL lokal Anda.
    *   Buat database baru dengan nama `sistem_pembayaran_smknula`.
    *   Klik menu **File** > **Load SQL file...**, pilih file `database/database.sql` yang ada di dalam proyek.
    *   Tekan tombol **F9** atau ikon *Execute SQL* untuk menjalankan script. Pastikan seluruh tabel (`petugas`, `siswa`, `jenis_pembayaran`, `transaksi`, `pengaturan`) dan dummy data berhasil dibuat.

3.  **Install Library Pendukung (Composer):**
    Buka Terminal atau Command Prompt, arahkan ke folder proyek Anda, lalu jalankan perintah berikut untuk memasang library ekspor dokumen:
    ```bash
    cd C:/xampp/htdocs/sistem_pembayaran_smknula
    composer require dompdf/dompdf phpoffice/phpspreadsheet phpoffice/phpword
    ```

4.  **Konfigurasi Database:**
    Buka file `config/database.php`. Sesuaikan username dan password MySQL Anda jika berbeda dari pengaturan bawaan:
    ```php
    $user = 'root';$pass = ''; // Isi jika database lokal Anda memiliki password
    ```

---

## 🔐 Akun Uji Coba (Default Kredensial)
Buka peramban (browser) Anda dan akses alamat: `http://localhost/sistem_pembayaran_smknula/`

Gunakan akun di bawah ini untuk masuk ke dalam sistem:
*   **Username:** `admin`
*   **Password:** `admin123`

---

## 🧪 Skenario Uji Coba Sistem (UAT)

Lakukan langkah-langkah berikut untuk menguji kelayakan sistem:

| No | Modul Uji | Langkah Kerja | Hasil yang Diharapkan | Status |
|---|---|---|---|---|
| 1 | **Login Form** | Masukkan username `admin` dan password salah. Kemudian coba masukkan password yang benar (`admin123`). | Muncul alert kesalahan jika salah. Jika benar, animasi loading berjalan dan dialihkan ke Dashboard. | 🟩 Lolos |
| 2 | **Dashboard** | Periksa nominal pada widget finansial dan grafik Chart.js. | Data pendapatan hari ini dan bulan ini langsung terhitung otomatis sesuai database. | 🟩 Lolos |
| 3 | **Pencarian Siswa** | Masuk ke menu **Transaksi Kasir**, ketik NIS `26271001` atau nama `Reyhan` lalu klik cari / tekan Enter. | Biodata, foto siswa, dan 5 riwayat transaksi terakhir langsung muncul di panel sebelah kiri via AJAX. | 🟩 Lolos |
| 4 | **Form Kasir & Hitung** | Pilih opsi pada dropdown *Jenis Pembayaran*, lalu masukkan nilai denda atau diskon. | Nominal utama muncul secara otomatis. Nilai *Grand Total* di bagian bawah berubah secara instan tanpa reload. | 🟩 Lolos |
| 5 | **Simpan & Cetak** | Klik tombol **Simpan Pembayaran & Cetak Struk**. | Muncul konfirmasi SweetAlert2. Setelah disetujui, data masuk ke database dan tab baru otomatis terbuka untuk mencetak struk thermal. | 🟩 Lolos |
| 6 | **Auto-Logout** | Biarkan aplikasi terbuka tanpa aktivitas selama 15 menit. | Sesi otomatis dihancurkan demi keamanan dan pengguna dialihkan kembali ke halaman login. | 🟩 Lolos |

---
© 2026 SMK NU Lamongan | Dikembangkan untuk Efisiensi & Transparansi Keuangan Sekolah.