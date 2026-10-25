<?php

/** Pemeriksaan instalasi lokal. Tidak mengubah database. */
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Pemeriksaan instalasi hanya tersedia dari localhost.');
}

require_once __DIR__ . '/src/support.php';

$checks = [];
$add = static function (bool $passed, string $label, string $detail = '') use (&$checks): void {
    $checks[] = ['passed' => $passed, 'label' => $label, 'detail' => $detail];
};

$add(PHP_VERSION_ID >= 80100, 'Versi PHP 8.1 atau lebih baru', 'Versi terdeteksi: ' . PHP_VERSION);
$add(extension_loaded('pdo_mysql'), 'Ekstensi pdo_mysql aktif', 'Wajib untuk akses MySQL / MariaDB melalui PDO.');
$add(extension_loaded('mbstring'), 'Ekstensi mbstring aktif', 'Digunakan untuk memproses dan memvalidasi teks UTF-8.');

if (extension_loaded('pdo_mysql')) {
    try {
        $connection = db();
        $add(true, 'Koneksi database MySQL', 'Database: ' . DB_NAME . ' • Host: ' . DB_HOST);

        $required = ['users', 'customers', 'couriers', 'shipments', 'tracking_events', 'depots'];
        $found = $connection->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $missing = array_values(array_diff($required, $found));
        $add(!$missing, 'Seluruh tabel tersedia', $missing ? 'Tabel belum ditemukan: ' . implode(', ', $missing) : implode(', ', $required));

        if (!$missing) {
            $roleColumns = $connection->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
            $add(in_array('role', $roleColumns, true) && in_array('courier_id', $roleColumns, true),
                'Struktur akun admin/kurir tersedia', 'Kolom users.role dan users.courier_id.');

            $admin = one("SELECT COUNT(*) AS total FROM users WHERE role='admin'");
            $courier = one("SELECT COUNT(*) AS total FROM users WHERE role='courier' AND courier_id IS NOT NULL");
            $add((int) ($admin['total'] ?? 0) > 0, 'Setidaknya satu akun admin tersedia');
            $add((int) ($courier['total'] ?? 0) > 0, 'Setidaknya satu akun kurir terhubung', 'Admin dapat membuat akun kurir dari menu Pengguna.');

            $shipments = one('SELECT COUNT(*) AS total FROM shipments');
            $add((int) ($shipments['total'] ?? 0) > 0, 'Ada data resi untuk uji tracking', 'Jika kosong, admin dapat membuat pengiriman baru.');
        }
    } catch (Throwable $exception) {
        error_log('KirimKita check.php: ' . $exception->getMessage());
        $add(false, 'Koneksi database MySQL', 'Periksa XAMPP MySQL, nama database, username, password, dan instalasi melalui install.php.');
    }
}

$passed = count(array_filter($checks, static fn (array $item): bool => $item['passed']));
$ready = $passed === count($checks);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Periksa Instalasi - KirimKita</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="install-body">
<main class="install-panel install-diagnostics">
    <div class="brand-icon">KK</div>
    <h1>Periksa Instalasi</h1>
    <p><?= e((string) $passed) ?> dari <?= e((string) count($checks)) ?> pemeriksaan berhasil.</p>
    <div class="check-list">
        <?php foreach ($checks as $item): ?>
            <div class="check-item">
                <span class="check-mark <?= $item['passed'] ? 'passed' : 'failed' ?>"><?= $item['passed'] ? '✓' : '!' ?></span>
                <div><strong><?= e($item['label']) ?></strong>
                    <?php if ($item['detail']): ?><small><?= e($item['detail']) ?></small><?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p><?= $ready ? 'Sistem siap diuji dengan akun admin, kurir, dan halaman tracking publik.' : 'Perbaiki pemeriksaan yang belum berhasil, lalu muat ulang halaman ini.' ?></p>
    <a class="btn btn-primary btn-full" href="index.php?page=login">Login Petugas</a>
    <a class="btn btn-light btn-full" href="index.php?page=tracking">Tracking Pelanggan</a>
    <a class="btn btn-light btn-full" href="install.php">Kembali ke Installer</a>
    <p class="small">Hapus check.php bersama install.php sebelum memasang aplikasi di hosting publik.</p>
</main>
</body>
</html>
