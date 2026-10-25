<?php

/**
 * KirimKita — satu pintu masuk untuk tiga pengalaman:
 * admin, kurir, dan pelacakan publik (pelanggan tanpa akun).
 */

require_once __DIR__ . '/src/support.php';

try {
    db()->query('SELECT id FROM users LIMIT 1');
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(503);
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Database KirimKita belum siap</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body class="install-body">
        <main class="install-panel">
            <h1>Database belum siap</h1>
            <p>Jalankan Apache dan MySQL di XAMPP, lalu periksa konfigurasi database.</p>
            <a class="btn btn-primary btn-full" href="install.php">Buka Instalasi</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// Aksi POST mempunyai pemeriksaan role sendiri di dalam src/actions.php.
require __DIR__ . '/src/actions.php';

$page = (string) ($_GET['page'] ?? (signed_in() ? 'dashboard' : 'tracking'));

if (!array_key_exists($page, page_roles())) {
    http_response_code(404);
    exit('404 - Halaman tidak ditemukan.');
}

if ($page === 'login' && signed_in()) {
    go('dashboard');
}

if (!can_access_page($page, 'guest')) {
    require_login();
}

// Server tetap menolak URL manual yang bukan hak akses role-nya.
if (!can_access_page($page, current_role())) {
    http_response_code(403);
    exit('403 - Akses ditolak untuk peran Anda.');
}

if ($page === 'export') {
    require __DIR__ . '/src/export.php';
    exit;
}

require_once __DIR__ . '/src/views/layout.php';

// Memisahkan sumber berdasarkan role tanpa membuat aplikasi / database terpisah.
foreach (['admin', 'courier', 'public', 'shared'] as $group) {
    foreach (glob(__DIR__ . '/src/pages/' . $group . '/*.php') ?: [] as $file) {
        require_once $file;
    }
}
require_once __DIR__ . '/src/pages/router.php';

render_page($page);
