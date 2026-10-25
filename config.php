<?php
/**
 * Konfigurasi bawaan (aman untuk dikomit ke GitHub).
 *
 * Untuk komputer pribadi, salin config.local.php.example menjadi config.local.php.
 * File config.local.php sengaja tidak dilacak oleh Git.
 */

$localConfig = __DIR__ . '/config.local.php';

if (is_file($localConfig)) {
    require_once $localConfig;
}

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
}

if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'kirimkita_db');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
}

if (!defined('APP_NAME')) {
    define('APP_NAME', 'KirimKita');
}

date_default_timezone_set('Asia/Jakarta');
