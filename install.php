<?php

/**
 * Installer lokal satu kali.
 * Untuk hosting publik, hapus file ini setelah mengimpor database dan membuat akun.
 */

require_once __DIR__ . '/src/support.php';

// Tidak boleh menjalankan installer yang membuat akun demo dari jaringan publik.
$clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
if (!in_array($clientIp, ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Instalasi hanya dapat dijalankan dari localhost.');
}

$installed = false;
$status = '';
$error = '';

try {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', DB_NAME)) {
        throw new RuntimeException('Nama database tidak valid.');
    }

    $server = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Jika instalasi sudah pernah berhasil, jangan isi data demo kembali.
    try {
        $server->exec('USE `' . DB_NAME . '`');
        $installed = (int) $server->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    } catch (PDOException $ignored) {
        $installed = false;
    }

    $needsUpgrade = $installed && !schema_is_current($server);

    if ($installed && $needsUpgrade && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['upgrade'])) {
        verify_csrf();
        create_schema($server);
        upgrade_schema($server);
        $needsUpgrade = false;
        $status = 'Database berhasil diperbarui untuk fitur tiga pengguna. Data lama tetap tersedia.';
    } elseif ($installed && $needsUpgrade) {
        $status = 'Versi database lama terdeteksi. Cadangkan database, kemudian klik Perbarui Struktur.';
    } elseif ($installed) {
        $status = 'Database sudah terpasang. Silakan masuk menggunakan akun Anda.';
    } elseif (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['install'])) {
        verify_csrf();
        $server->exec(
            'CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` '
            . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
        $server->exec('USE `' . DB_NAME . '`');
        create_schema($server);
        upgrade_schema($server);
        seed_demo_data($server);

        $installed = true;
        $status = 'Instalasi berhasil! Admin: admin@kirimkita.id / admin12345. Kurir: kurir@kirimkita.id / kurir12345.';
    }
} catch (Throwable $exception) {
    error_log('KirimKita setup: ' . $exception->getMessage());
    $error = 'Instalasi belum berhasil. Pastikan MySQL menyala serta konfigurasi database sesuai.';
}

/** Menandai versi lama yang belum memiliki kolom role/relasi kurir. */
function schema_is_current(PDO $connection): bool
{
    $columns = $connection->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
    $tables = $connection->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['users', 'customers', 'couriers', 'shipments', 'tracking_events', 'depots'];

    return count(array_diff($requiredTables, $tables)) === 0
        && in_array('role', $columns, true)
        && in_array('courier_id', $columns, true)
        && has_courier_fk($connection);
}

/** Periksa constraint relasi akun kurir pada MySQL/MariaDB. */
function has_courier_fk(PDO $connection): bool
{
    $statement = $connection->prepare('
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
    ');
    $statement->execute([DB_NAME, 'users', 'fk_user_courier']);

    return (int) $statement->fetchColumn() > 0;
}

/** Upgrade non-destruktif data lama, aman dijalankan kembali. */
function upgrade_schema(PDO $connection): void
{
    $columns = $connection->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('role', $columns, true)) {
        $connection->exec("ALTER TABLE users ADD COLUMN role ENUM('admin','courier') NOT NULL DEFAULT 'admin'");
    }

    if (!in_array('courier_id', $columns, true)) {
        $connection->exec('ALTER TABLE users ADD COLUMN courier_id INT UNSIGNED NULL UNIQUE');
    }

    if (!has_courier_fk($connection)) {
        $connection->exec('
            ALTER TABLE users
            ADD CONSTRAINT fk_user_courier
            FOREIGN KEY (courier_id) REFERENCES couriers(id)
            ON DELETE RESTRICT
        ');
    }
}

/** Menjalankan setiap CREATE TABLE dari skema yang disertakan. */
function create_schema(PDO $connection): void
{
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');

    if ($sql === false) {
        throw new RuntimeException('Skema database tidak ditemukan.');
    }

    foreach (explode(';', $sql) as $statement) {
        $statement = preg_replace('/^\s*--[^\n]*\n/m', '', $statement);
        $statement = trim($statement);

        if ($statement !== '' && stripos($statement, 'CREATE TABLE') === 0) {
            $connection->exec($statement);
        }
    }
}

/** Akun dan data fiktif untuk kebutuhan demonstrasi tugas besar. */
function seed_demo_data(PDO $connection): void
{
    $connection->beginTransaction();

    try {
        $connection->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)')
            ->execute([
                'Administrator',
                'admin@kirimkita.id',
                password_hash('admin12345', PASSWORD_DEFAULT),
            ]);

        $customers = [
            ['Andi Saputra', '081234567890', 'andi@email.com', 'Jl. Sukajadi No. 12, Bandung'],
            ['Siti Nurhaliza', '081298765432', 'siti@email.com', 'Jl. Asia Afrika No. 18, Bandung'],
            ['Budi Santoso', '081376543210', 'budi@email.com', 'Jl. Merdeka No. 5, Jakarta'],
            ['Dewi Lestari', '081312340987', 'dewi@email.com', 'Jl. Riau No. 25, Bandung'],
            ['Rizky Pratama', '082112345678', 'rizky@email.com', 'Jl. Gatot Subroto No. 9, Bandung'],
        ];

        $couriers = [
            ['Dimas Prakoso', '081355551111', 'Motor'],
            ['Fajar Ramadhan', '081355552222', 'Mobil Box'],
            ['Nadia Putri', '081355553333', 'Motor'],
            ['Rangga Aditya', '081355554444', 'Van'],
        ];

        $shipments = [
            [1, 1, 'Rina Kurnia', '081311223344', 'Bandung', 'Jl. Dago No. 55, Bandung', 'REG', 2.5, 18000, 'pending'],
            [2, 2, 'Putra Mahendra', '081322334455', 'Jakarta', 'Jl. Kuningan No. 10, Jakarta', 'EXPRESS', 1.2, 38000, 'in_transit'],
            [3, 3, 'Ayu Permatasari', '081333445566', 'Surabaya', 'Jl. Tunjungan No. 8, Surabaya', 'REG', 3.0, 32000, 'delivered'],
            [4, 4, 'Hendra Wijaya', '081344556677', 'Bekasi', 'Jl. Ahmad Yani No. 20, Bekasi', 'EXPRESS', 0.8, 29000, 'picked_up'],
            [5, 1, 'Nina Oktavia', '081355667788', 'Semarang', 'Jl. Pandanaran No. 5, Semarang', 'REG', 4.1, 42000, 'in_transit'],
            [1, 2, 'Mega Puspita', '081366778899', 'Bandung', 'Jl. Setiabudi No. 91, Bandung', 'REG', 1.4, 15000, 'delivered'],
        ];

        $customerInsert = $connection->prepare(
            'INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)'
        );
        foreach ($customers as $item) {
            $customerInsert->execute($item);
        }

        $courierInsert = $connection->prepare(
            'INSERT INTO couriers (name, phone, vehicle) VALUES (?, ?, ?)'
        );
        foreach ($couriers as $item) {
            $courierInsert->execute($item);
        }

        $connection->prepare('INSERT INTO users (name, email, password_hash, role, courier_id) VALUES (?, ?, ?, ?, ?)')
            ->execute(['Dimas Prakoso', 'kurir@kirimkita.id', password_hash('kurir12345', PASSWORD_DEFAULT), 'courier', 1]);

        $connection->prepare('INSERT INTO depots (name, city, address, phone) VALUES (?, ?, ?, ?)')
            ->execute(['KirimKita Hub Bandung', 'Bandung', 'Jl. Soekarno Hatta No. 100, Bandung', '0221234567']);

        $shipmentInsert = $connection->prepare('
            INSERT INTO shipments (
                tracking_number, customer_id, courier_id, recipient_name,
                recipient_phone, origin_address, destination_address,
                destination_city, service_type, weight_kg, shipping_cost, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $eventInsert = $connection->prepare(
            'INSERT INTO tracking_events (shipment_id, status, description) VALUES (?, ?, ?)'
        );

        foreach ($shipments as $index => $item) {
            [
                $customerId, $courierId, $recipient, $phone, $city, $address,
                $service, $weight, $price, $shipmentStatus,
            ] = $item;

            $tracking = 'KK' . date('ymd') . str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT);
            $shipmentInsert->execute([
                $tracking, $customerId, $courierId, $recipient, $phone,
                $customers[$customerId - 1][3], $address, $city,
                $service, $weight, $price, $shipmentStatus,
            ]);

            $shipmentId = (int) $connection->lastInsertId();
            $eventInsert->execute([$shipmentId, 'pending', 'Paket dibuat dan menunggu dijemput.']);

            // Seed histori lengkap agar contoh timeline terlihat realistis.
            $stages = ['picked_up', 'in_transit', 'delivered'];
            if ($shipmentStatus === 'cancelled') {
                $eventInsert->execute([$shipmentId, 'cancelled', 'Pengiriman dibatalkan.']);
            } else {
                foreach ($stages as $stage) {
                    if ($shipmentStatus === 'pending') {
                        break;
                    }
                    $eventInsert->execute([
                        $shipmentId,
                        $stage,
                        'Status diperbarui: ' . status_name($stage) . '.',
                    ]);
                    if ($stage === $shipmentStatus) {
                        break;
                    }
                }
            }
        }

        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollBack();
        throw $exception;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalasi KirimKita</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="install-body">
    <main class="install-panel">
        <div class="brand-icon">KK</div>
        <h1>Setup KirimKita</h1>
        <p>Persiapan database dan data contoh untuk tugas besar Manajemen Proyek.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php elseif ($status): ?>
            <div class="alert alert-success"><?= e($status) ?></div>
        <?php endif; ?>

        <?php if ($installed && ($needsUpgrade ?? false) && !$error): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="upgrade" value="1">
                <button class="btn btn-primary btn-full" type="submit">Perbarui Struktur Database</button>
            </form>
        <?php endif; ?>

        <?php if (!$installed && !$error): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="install" value="1">
                <button class="btn btn-primary btn-full" type="submit">
                    Pasang Database & Data Contoh
                </button>
            </form>
        <?php endif; ?>

        <p class="small">Database: <b><?= e(DB_NAME) ?></b> • MySQL: <?= e(DB_HOST) ?></p>
        <a class="btn btn-light btn-full" href="check.php">Periksa Kesiapan Sistem</a>
        <a class="btn btn-light btn-full" href="index.php?page=login">Buka Halaman Login →</a>
    </main>
</body>
</html>
