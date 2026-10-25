# Panduan KirimKita dari nol — XAMPP, 3 Pengguna, dan GitHub

Panduan khusus Windows untuk tugas besar Manajemen Proyek. KirimKita menggunakan **satu folder proyek** dan **satu database MySQL**. Login hanya untuk admin dan kurir; pelanggan (user) mengakses tracking publik tanpa akun.

## A. Siapkan komputer

1. Pasang **XAMPP** dengan PHP 8.1 atau lebih baru, Apache, MySQL/MariaDB, ekstensi `pdo_mysql`, dan `mbstring`.
2. Instal **Visual Studio Code** jika ingin mengedit kode, dan **Git for Windows** jika akan mengunggah dengan terminal.
3. Ekstrak ZIP. Nama folder sebaiknya `kirimkita`, bukan folder ZIP bersarang.
4. Salin folder itu ke `C:\xampp\htdocs\kirimkita`.
5. Di **XAMPP Control Panel**, klik **Start** pada Apache dan MySQL; keduanya harus berwarna hijau.
6. Akses `http://localhost/kirimkita/install.php` (jika Apache di port 8080: `http://localhost:8080/kirimkita/install.php`).
7. Klik **Pasang Database & Data Contoh**. Installer membuat enam tabel dan akun demo secara otomatis.
8. Buka `http://localhost/kirimkita/check.php`. Semua pemeriksaan idealnya berhasil.
9. Buka `http://localhost/kirimkita/index.php?page=login` untuk petugas atau `http://localhost/kirimkita/index.php?page=tracking` untuk pelanggan.

**Pengaturan MySQL:** Default XAMPP adalah username `root`, password kosong. Jika berbeda, salin `config.local.php.example` menjadi `config.local.php` lalu ubah nilai `DB_USER` dan `DB_PASS`. `config.local.php` tidak boleh dipush ke GitHub.

> Jika sebelumnya sudah memiliki database KirimKita, **backup lewat phpMyAdmin** sebelum menekan tombol *Perbarui Struktur*. Installer tidak menghapus data lama, tetapi backup tetap wajib untuk menghindari kehilangan data karena kesalahan konfigurasi.

## B. Akun demo setelah instalasi baru

| Pengguna | Email / akses | Password |
| --- | --- | --- |
| Admin | `admin@kirimkita.id` | `admin12345` |
| Kurir | `kurir@kirimkita.id` | `kurir12345` |
| Pelanggan | Buka halaman tracking tanpa login | Tidak diperlukan |

**Catatan:** Akun demo hanya dibuat pada instalasi database **baru**. Jika upgrade dari versi lama, admin bisa menambah kurir di **Armada / Kurir**, kemudian membuat akun pada menu **Pengguna**.

## C. Demo ketiga pengguna (lakukan berurutan)

1. **Admin:** login → buka **Pelanggan**, tambahkan pelanggan → buka **Armada / Kurir** dan pastikan ada kurir → buka **Pengiriman** → **Tambah Pengiriman** → isi nama penerima, alamat, berat, ongkir, layanan → pilih kurir dengan akun → **Buat Pengiriman**.
2. Di halaman detail pengiriman, **salin nomor resi**. Klik **Cetak Resi** untuk membuka label berisi barcode Code39 dan klik **Cetak Resi** untuk membuka dialog printer.
3. **Kurir:** keluar dari akun admin atau gunakan browser *Incognito* lain → masuk sebagai kurir → buka **Tugas Pengiriman**. Resi yang ditugaskan akan muncul. Klik pembaruan status bertahap: **Menunggu Pickup → Sudah Dijemput → Dalam Perjalanan → Terkirim**. Catatan perjalanan opsional.
4. Di **Scan / Cari Paket**, ketik nomor resi dan klik **Cari Paket**. Jika browser mendukung `BarcodeDetector` dan kamera, klik **Scan menggunakan kamera**, arahkan barcode label ke kamera. Jika tidak didukung, input manual tetap berfungsi.
5. **Pelanggan:** buka `http://localhost/kirimkita/index.php?page=tracking` tanpa login → masukkan resi yang disalin admin → klik **Lacak Paket**. Tampilkan status akhir dan *timeline* yang bertambah setelah setiap pembaruan kurir.
6. **Admin:** buka menu **Laporan** → pilih filter → **Export CSV**, lalu buka hasil unduhan di Excel jika diperlukan.

Perubahan status hanya tersedia untuk **paket yang ditugaskan** kepada kurir tersebut. Jika paket tidak muncul di kurir, edit pengiriman sebagai admin dan pilih kurir yang sesuai.

## D. Tabel skenario pengujian manual

| No. | Aksi yang diuji | Hasil yang diharapkan |
| --- | --- | --- |
| 1 | Instalasi baru | Database, tabel, dan data contoh dibuat |
| 2 | Login admin benar | Dashboard admin terbuka |
| 3 | Login password salah | Ditolak dengan pesan error |
| 4 | Tambah/edit/hapus pelanggan baru | Data berubah; pelanggan yang punya pengiriman tidak bisa dihapus |
| 5 | Tambah kurir lalu buat akun kurir | Kurir bisa login dengan akun sendiri |
| 6 | Buat pengiriman admin | Resi unik muncul dan berstatus menunggu pickup |
| 7 | Cetak resi admin | Label dan barcode muncul, dialog cetak tersedia |
| 8 | Login kurir | Hanya melihat pengiriman yang ditugaskan kepadanya |
| 9 | Kurir ubah status bertahap | Status berubah dan timeline bertambah |
| 10 | Kurir akses halaman admin lewat URL manual | Ditolak (403) |
| 11 | Pelanggan akses tracking tanpa login | Status dan timeline tampil, tanpa data pribadi lengkap |
| 12 | Admin filter laporan lalu export CSV | Data CSV berhasil diunduh |
| 13 | Admin/kurir ubah profil dan password | Data login akun berubah |
| 14 | Keluar dari akun lalu buka dashboard | Dialihkan ke halaman login |

## E. Upload dengan Git Bash / Terminal VS Code

1. Buat repository kosong bernama `kirimkita` di GitHub, misalnya `https://github.com/USERNAME/kirimkita`.
2. Buka terminal pada folder proyek (`C:\xampp\htdocs\kirimkita`), lalu jalankan:

```bash
git init
git add .
git commit -m "feat: aplikasi KirimKita tiga pengguna siap demo"
git branch -M main
git remote add origin https://github.com/USERNAME/kirimkita.git
git push -u origin main
```

Ganti `USERNAME` dengan nama akun GitHub. Jika repository sudah pernah diinisialisasi, **jangan ulangi** `git init` dan `git remote add origin`, cukup `git add .`, `git commit`, `git push`.

Cek berkas yang akan dikirim sebelum commit:

```bash
git status
git ls-files
```

**Jangan unggah:** `config.local.php` (berisi password database), berkas backup, atau data pelanggan sungguhan. File `.gitignore` sudah disediakan.

GitHub menyimpan kode sumber, **bukan menjalankan PHP/MySQL**. GitHub Pages tidak bisa menjalankan aplikasi ini. Untuk demo di kelas, gunakan XAMPP; untuk demo melalui internet, gunakan hosting yang mendukung PHP + MySQL, ubah semua password default, nonaktifkan `install.php` dan `check.php`, dan pakai HTTPS.

## F. Jika mengalami error

| Pesan atau gejala | Perbaikan |
| --- | --- |
| `localhost refused to connect` | Periksa Apache XAMPP dan port yang dipakai. |
| `Database belum siap` | Start MySQL, buka `install.php`, periksa `config.local.php`. |
| `could not find driver` | Aktifkan `pdo_mysql` di PHP XAMPP, restart Apache. |
| `Call to undefined function mb_*` | Aktifkan `mbstring` di PHP XAMPP, restart Apache. |
| `404 Not Found` | Pastikan folder tepat: `htdocs/kirimkita/index.php`, tidak bersarang. |
| Login admin/kurir gagal | Periksa akun contoh dari instalasi baru dan huruf email/password; gunakan reset password di admin jika perlu. |
| Kurir tidak melihat paket | Pilih kurir yang terkait dengan akun pada edit pengiriman. |
| Nomor resi tidak ditemukan | Salin persis nomor resi dari halaman detail pengiriman admin. |
| Kamera tidak tersedia | Gunakan input manual/USB scanner. Scan kamera hanya pada browser yang mendukung API dan konteks aman (`localhost` atau HTTPS). |
| Git `remote origin already exists` | Gunakan `git remote -v`, lalu `git remote set-url origin https://github.com/USERNAME/kirimkita.git`. |
| Halaman kosong / 500 | Cek log Apache XAMPP dan pastikan PHP 8.1+ serta ekstensi aktif. |

## G. Apa yang memang belum ada?

Ini aplikasi tugas kuliah: belum ada integrasi tarif ekspedisi sebenarnya, GPS real-time, WhatsApp, notifikasi otomatis, dan pembayaran. *Real-time* pada halaman tracking berarti data paling baru ketika halaman dimuat ulang setelah kurir memperbarui status, bukan streaming GPS. Tidak memerlukan internet atau API eksternal untuk fungsi dasar.
