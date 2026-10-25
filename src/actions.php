<?php

/**
 * Proses semua formulir (POST).
 * Keberhasilan diarahkan dengan pola Post/Redirect/Get supaya tidak terkirim ulang.
 */

require_once __DIR__ . '/actions/admin.php';
require_once __DIR__ . '/actions/courier.php';
require_once __DIR__ . '/actions/account.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    return;
}

verify_csrf();
$action = (string) ($_POST['action'] ?? '');

if ($action === 'login') {
    $email = strtolower(form_string('email', 190));
    $password = (string) ($_POST['password'] ?? '');
    $user = one('SELECT id, password_hash, role, courier_id FROM users WHERE email = ?', [$email]);

    if ($user && password_verify($password, $user['password_hash'])) {
        if ($user['role'] === 'courier' && !$user['courier_id']) {
            flash('error', 'Akun kurir belum terhubung. Hubungi admin.');
            go('login');
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        flash('success', 'Selamat datang! Anda berhasil masuk.');
        go('dashboard');
    }

    flash('error', 'Email atau password salah.');
    go('login');
}

require_login();

try {
    switch ($action) {
        case 'logout':
            unset($_SESSION['user_id'], $_SESSION['csrf']);
            session_regenerate_id(true);
            flash('success', 'Anda berhasil keluar.');
            go('login');

        case 'courier_update_status':
            require_role('courier');
            courier_update_status();
            break;

        case 'save_user':
            require_role('admin');
            save_user();
            break;

        case 'reset_user_password':
            require_role('admin');
            reset_user_password();
            break;

        case 'delete_user':
            require_role('admin');
            delete_user();
            break;

        case 'save_depot':
            require_role('admin');
            save_depot();
            break;

        case 'delete_depot':
            require_role('admin');
            delete_depot();
            break;

        case 'save_customer':
            require_role('admin');
            save_customer();
            break;

        case 'delete_customer':
            require_role('admin');
            delete_customer();
            break;

        case 'save_courier':
            require_role('admin');
            save_courier();
            break;

        case 'delete_courier':
            require_role('admin');
            delete_courier();
            break;

        case 'save_shipment':
            require_role('admin');
            save_shipment();
            break;

        case 'update_status':
            require_role('admin');
            update_shipment_status();
            break;

        case 'delete_shipment':
            require_role('admin');
            delete_shipment();
            break;

        case 'update_profile':
            update_profile();
            break;

        case 'change_password':
            change_password();
            break;

        default:
            throw new DomainException('Aksi tidak dikenali.');
    }
} catch (DomainException $exception) {
    flash('error', $exception->getMessage());
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    flash('error', 'Operasi database gagal. Periksa input atau hubungi administrator.');
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    flash('error', 'Terjadi kesalahan saat menyimpan data.');
}

// Kembali ke halaman yang diizinkan, bukan URL yang dikirim sembarang pengguna.
$destination = (string) ($_POST['return_page'] ?? 'dashboard');
$allowedPages = [
    'dashboard', 'shipments', 'shipment', 'shipment-form',
    'customers', 'couriers', 'depots', 'users', 'profile',
    'courier-tasks', 'courier-scan', 'courier-history',
];

if (!in_array($destination, $allowedPages, true)
    || !can_access_page($destination, current_role())) {
    $destination = 'dashboard';
}

$id = max(0, (int) ($_POST['id'] ?? 0));
go($destination, $id > 0 ? ['id' => $id] : []);
