<?php

/**
 * Pengujian fungsi dasar tanpa membutuhkan database MySQL.
 * Jalankan dengan: php tests/smoke.php
 */

require_once dirname(__DIR__) . '/src/support.php';

function verify(bool $condition, string $description): void
{
    if (!$condition) {
        fwrite(STDERR, "[FAIL] {$description}\n");
        exit(1);
    }

    echo "[PASS] {$description}\n";
}

verify(e('<script>') === '&lt;script&gt;', 'HTML escape mencegah injeksi tag');
verify(rupiah(15000) === 'Rp 15.000', 'Format mata uang rupiah');
verify(status_name('delivered') === 'Terkirim', 'Label status pengiriman');
verify(isset(status_options()['pending']), 'Status awal tersedia');
verify(is_valid_date('2026-10-25'), 'Validasi tanggal valid');
verify(!is_valid_date('2026-02-30'), 'Menolak tanggal tidak valid');
verify(url('shipment', ['id' => 42]) === 'index.php?page=shipment&id=42', 'Pembuatan URL');
verify((bool) preg_match('/^KK\d{6}[A-F\d]{8}$/', tracking_code()), 'Format resi acak');
verify(strlen(token()) === 48, 'Token CSRF tersedia');
verify(strlen(money_input(15000)) > 0, 'Format input ongkir');

// Uji rentang tanggal tanpa mengakses database.
$from = '2026-02-30';
$to = '2026-10-25';
check_date_filters($from, $to);
verify($from === '' && $to === '2026-10-25', 'Filter tanggal menghapus nilai tidak valid');

// Kontrol akses diuji tanpa MySQL: hanya halaman publik untuk pelanggan.
verify(can_access_page('tracking', 'guest'), 'Pelanggan dapat membuka tracking tanpa login');
verify(!can_access_page('shipments', 'guest'), 'Pelanggan tidak dapat membuka menu admin');
verify(can_access_page('shipments', 'admin'), 'Admin mengakses data pengiriman');
verify(can_access_page('receipt', 'admin'), 'Admin boleh mencetak label pengiriman');
verify(!can_access_page('receipt', 'courier'), 'Kurir tidak boleh membuka label admin');
verify(!can_access_page('receipt', 'guest'), 'Tracking publik tidak dapat mengakses label privat');
verify(!can_access_page('users', 'courier'), 'Kurir tidak dapat mengelola akun petugas');
verify(can_access_page('courier-tasks', 'courier'), 'Kurir dapat membuka tugasnya');
verify(!can_access_page('courier-tasks', 'admin'), 'Admin tidak membuka halaman khusus kurir');
verify(!can_access_page('nama-tidak-dikenal', 'admin'), 'Halaman yang tidak dikenal ditolak');
verify(next_courier_status('pending') === 'picked_up', 'Kurir: pickup sesuai urutan');
verify(next_courier_status('picked_up') === 'in_transit', 'Kurir: lanjut ke perjalanan');
verify(next_courier_status('in_transit') === 'delivered', 'Kurir: status akhir terkirim');
verify(next_courier_status('cancelled') === null, 'Status dibatalkan tidak dapat dilanjutkan');
verify(next_courier_status('delivered') === null, 'Status terkirim tidak dapat dilanjutkan');

// Muat semua modul tampilan untuk mendeteksi nama fungsi yang bentrok.
require_once dirname(__DIR__) . '/src/views/layout.php';
foreach (['admin', 'courier', 'public', 'shared'] as $roleFolder) {
    foreach (glob(dirname(__DIR__) . '/src/pages/' . $roleFolder . '/*.php') ?: [] as $file) {
        require_once $file;
    }
}
require_once dirname(__DIR__) . '/src/pages/router.php';
require_once dirname(__DIR__) . '/src/actions/admin.php';
require_once dirname(__DIR__) . '/src/actions/courier.php';
require_once dirname(__DIR__) . '/src/actions/account.php';

verify(function_exists('page_users'), 'Halaman pengguna admin tersedia');
verify(function_exists('page_receipt'), 'Halaman cetak resi admin tersedia');
$barcode = shipment_barcode('KK26102500000001');
verify(str_contains($barcode, '<svg') && str_contains($barcode, '<rect'), 'Generator barcode Code39 menghasilkan SVG');
verify(shipment_barcode('INVALID<>') === '', 'Barcode menolak input yang tidak didukung');
verify(function_exists('page_courier_dashboard'), 'Dashboard kurir tersedia');
verify(function_exists('page_courier_tasks'), 'Tugas kurir tersedia');
verify(function_exists('page_tracking'), 'Halaman tracking publik tersedia');
verify(function_exists('courier_update_status'), 'Backend update kurir tersedia');

// Login dan tracking awal harus dapat dirender tanpa tersambung ke MySQL.
ob_start();
page_login();
$loginHtml = (string) ob_get_clean();
verify(str_contains($loginHtml, 'Masuk ke Sistem'), 'Rendering form login petugas');

ob_start();
page_tracking();
$trackingHtml = (string) ob_get_clean();
verify(str_contains($trackingHtml, 'Lacak Paket'), 'Rendering tracking pelanggan tanpa login');

$scannerSource = (string) file_get_contents(dirname(__DIR__) . '/assets/app.js');
verify(str_contains($scannerSource, 'BarcodeDetector'), 'Scanner kamera punya deteksi format barcode');
$scanPhp = (string) file_get_contents(dirname(__DIR__) . '/src/pages/courier/scan.php');
verify(str_contains($scanPhp, 'data-camera-scanner'), 'Halaman kurir menampilkan kontrol scan kamera');

echo "\nSemua pengujian dasar berhasil.\n";
