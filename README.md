# KirimKita 📦 — Web Manajemen Pengiriman 3 Pengguna

**Tugas Besar Manajemen Proyek** — satu aplikasi PHP/MySQL dengan **tiga pengalaman pengguna berbeda**. Tampilan menyesuaikan jenis pengguna, sedangkan proses dan database tetap terpusat.

> **Status:** prototipe akademik, bukan sistem ekspedisi produksi. Tanpa Laravel, Composer, Node.js, dan API eksternal untuk menjalankan aplikasi. Dibuat agar mudah dipasang di XAMPP dan disimpan di GitHub.

**Panduan Windows dari nol:** [`docs/PANDUAN_XAMPP_GITHUB.md`](docs/PANDUAN_XAMPP_GITHUB.md). Setelah instalasi, buka `http://localhost/kirimkita/check.php` untuk memastikan semua komponen siap.

## Pengguna dan Hak Akses

| Pengguna | Autentikasi | Halaman & fungsi |
| --- | --- | --- |
| **Admin** | Login email/password | Dashboard, CRUD pengiriman, pelanggan, armada/kurir, depo, akun petugas, laporan CSV, profil |
| **Kurir** | Login email/password | Dashboard khusus kurir, tugas yang ditugaskan kepadanya, pencarian / input resi, ubah status secara berurutan, riwayat, profil |
| **Pelanggan / User** | **Tidak perlu login** | Halaman tracking publik: input nomor resi, lihat status, kota tujuan, layanan, dan riwayat perjalanan |

**Penting:** pelanggan disebut *pengguna publik* (guest), **bukan akun ketiga di tabel `users`**. Yang login hanya admin dan kurir. Desain ini disengaja karena kebutuhan pelanggan sebatas tracking resi.

### Alur kerja

```text
Admin login → buat data pelanggan dan kurir → buat pengiriman & assign kurir
                                                        ↓
Kurir login → hanya lihat tugas sendiri → update status paket secara berurutan
                                                        ↓
Pelanggan / user → akses tracking tanpa login → ketik resi → lihat status baru
```

Satu perubahan status tersimpan ke `shipments` dan `tracking_events` dalam satu transaksi. Admin, kurir, dan halaman publik membaca database MySQL yang sama.

## Teknologi

- PHP 8.1+ (PHP native dan PDO MySQL), MySQL / MariaDB, HTML, CSS, JavaScript.
- Apache/XAMPP untuk localhost.
- GitHub Actions untuk pemeriksaan sintaks dan tes fungsi dasar.
- Tidak menggunakan framework tambahan maupun akun cloud.

## Struktur repository

```text
kirimkita/
├── assets/
│   ├── style.css                # Desain responsif: merah, putih, sidebar gelap
│   └── app.js                   # Sidebar, konfirmasi, tombol, pilihan role
├── database/
│   └── schema.sql               # Satu database dan relasi tabel
├── docs/
│   └── DOKUMENTASI_PROYEK.md   # Kebutuhan, aktor, WBS, ERD, skenario demo
├── src/
│   ├── actions/
│   │   ├── admin.php            # Operasi data & akun khusus admin
│   │   ├── courier.php          # Update status + verifikasi tugas kurir
│   │   └── account.php          # Profil dan ubah password
│   ├── pages/
│   │   ├── admin/               # UI admin (dashboard, pengiriman, dll.)
│   │   ├── courier/             # UI kurir (dashboard, tugas, cari, riwayat)
│   │   ├── public/              # UI pelanggan tracking + login petugas
│   │   ├── shared/              # Halaman profil petugas
│   │   └── router.php           # Pemilihan halaman
│   ├── views/
│   │   └── layout.php           # Navigasi & komponen berdasarkan role
│   ├── support.php              # DB, session, role, CSRF, helper
│   ├── actions.php              # Dispatcher POST, guard izin di backend
│   └── export.php               # Laporan CSV khusus admin
├── tests/smoke.php              # Tes helper, status berurutan, hak akses
├── .github/workflows/           # Lint & smoke tests otomatis
├── .gitignore                   # Mengecualikan password konfigurasi lokal
├── .editorconfig                # Aturan format editor
├── config.local.php.example     # Contoh config; aman untuk GitHub
├── config.php                   # Default config (tanpa rahasia)
├── index.php                    # Front controller, cek role per URL
├── install.php                  # Installer lokal & migrasi skema lama
├── check.php                    # Pemeriksaan kesiapan XAMPP dan MySQL
└── README.md
```

**Tidak perlu tiga repository, tiga backend, atau tiga database.** Kode halaman dipisahkan agar rapi, fungsi bersama tidak digandakan. Kontrol akses diuji di backend; menyembunyikan menu saja tidak cukup.

## Cara menjalankan di XAMPP

1. Instal XAMPP dengan PHP 8.1+ dan ekstensi `pdo_mysql`, `mbstring`.
2. Ekstrak project ini menjadi folder `C:\xampp\htdocs\kirimkita` (jangan sampai bersarang `kirimkita/kirimkita`).
3. Jalankan **Apache** dan **MySQL**.
4. Apabila user/password MySQL berbeda dari bawaan XAMPP, salin `config.local.php.example` ke `config.local.php` lalu sesuaikan. File pribadi ini sudah masuk `.gitignore`.
5. Buka **http://localhost/kirimkita/install.php** lalu klik **Pasang Database & Data Contoh**.
6. Buka **http://localhost/kirimkita/** untuk halaman tracking publik, atau **http://localhost/kirimkita/index.php?page=login** untuk petugas.

### Akun demo

| Jenis | Email | Password awal |
| --- | --- | --- |
| Admin | `admin@kirimkita.id` | `admin12345` |
| Kurir (Dimas) | `kurir@kirimkita.id` | `kurir12345` |
| Pelanggan | Tidak perlu akun | Masukkan resi dari admin |

> Akun contoh hanya dibuat pada instalasi **baru**. Bila database sudah terpasang dari versi admin sebelumnya, `install.php` menawarkan peningkatan skema. **Backup database dulu**, jalankan peningkatan, lalu admin dapat membuat akun kurir melalui menu **Pengguna**. Data lama tidak dihapus; demo kurir belum otomatis tersedia pada instalasi lama. Ganti password demo sebelum hosting publik.

### Mencoba tiga pengalaman pengguna

1. **Admin:** login, buat pelanggan dan kurir (menu **Armada / Kurir**), lalu buat **Pengiriman** dan pilih kurirnya. Salin nomor resi.
2. **Kurir:** login sebagai kurir yang ditugaskan. Buka **Tugas Pengiriman** atau **Scan / Cari Paket**. Perbarui status `Menunggu Pickup → Sudah Dijemput → Dalam Perjalanan → Terkirim`.
3. **User:** buka **http://localhost/kirimkita/index.php?page=tracking** di tab browser lain. Masukkan nomor resi yang sama dan lihat status serta timeline terbaru.

**Scan / Cari Paket** menerima input manual atau scanner USB/Bluetooth. Scanner kamera juga tersedia pada browser yang mendukung API `BarcodeDetector` di `localhost` atau HTTPS; bila tidak tersedia, input manual tetap berjalan. Admin bisa mencetak label resi dengan barcode Code39 melalui tombol **Cetak Resi** pada detail pengiriman. Halaman publik tidak memuat data pribadi lengkap pengirim/penerima.

## Upload ke GitHub

Buat repository GitHub **kosong** bernama `kirimkita`, kemudian buka terminal di dalam direktori proyek:

```bash
git init
git add .
git commit -m "feat: kirimkita 3 user roles"
git branch -M main
git remote add origin https://github.com/USERNAME/kirimkita.git
git push -u origin main
```

Ganti `USERNAME` dengan username GitHub kamu. Jika sudah ada repository dari versi lama, **backup dahulu**, ganti isi source code dengan versi ini, lalu gunakan `git add .`, `git commit`, dan `git push`; jangan menjalankan `git init`/`git remote add` ulang.

Alternatif: pilih **Add file → Upload files** di GitHub dan unggah isi folder, bukan ZIP. Sertakan `.github`, `.gitignore`, `.editorconfig`. **Jangan** unggah `config.local.php` ataupun file konfigurasi database pribadi.

**GitHub bukan hosting PHP/MySQL.** GitHub Pages tidak bisa menjalankan backend PHP dan database. Gunakan XAMPP atau hosting PHP + MySQL untuk demo online.

## Pengujian

```bash
# Periksa seluruh file PHP (Linux/macOS/Git Bash)
find . -name '*.php' -print0 | xargs -0 -n1 php -l

# Smoke test tanpa database
php tests/smoke.php

# JavaScript (opsional, jika Node terpasang di komputer pengembang)
node --check assets/app.js
```

GitHub Actions menjalankan pemeriksaan yang sama setiap push dan pull request. **Smoke test tidak menggantikan pengujian login, CRUD, dan perubahan status dengan MySQL nyata.** Lakukan uji manual di XAMPP dengan dua akun login sebelum presentasi.

## Keamanan & batasan

- Password disimpan dengan `password_hash`, password login diverifikasi, session ID diperbarui setelah login.
- Semua perubahan data memakai CSRF token dan prepared statement.
- Kurir hanya bisa memproses paket miliknya; perubahan status harus berurutan, dicek pada server menggunakan transaksi database.
- Endpoint admin/kurir dicek pada server (`src/support.php`, `index.php`, dan dispatcher POST).
- Pelanggan hanya melihat informasi pelacakan publik dengan resi tepat, tanpa nomor telepon/alamat lengkap.
- Hapus/nonaktifkan **`install.php`** dan **`check.php`** sebelum deploy ke internet dan ubah password contoh. Untuk aplikasi sungguhan diperlukan rate limiting, audit log, HTTPS, backup, dan uji keamanan tambahan.

Dokumentasi proyek, ERD, WBS, kebutuhan dan skenario uji ada di [`docs/DOKUMENTASI_PROYEK.md`](docs/DOKUMENTASI_PROYEK.md).

## Catatan penting pengujian

Pengecekan sintaks dan smoke test tidak dapat menjamin koneksi MySQL di setiap komputer. Jalankan `check.php` di XAMPP dan ikuti 14 skenario pengujian dalam panduan Windows.
