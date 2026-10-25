<?php

/**
 * Helper global: database, otentikasi, CSRF, filter, dan format tampilan.
 * Semua query dinamis memakai prepared statements.
 */

require_once dirname(__DIR__) . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO
{
    static $connection = null;

    if ($connection === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $connection = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    return $connection;
}

/** Escape semua data tak tepercaya sebelum ditampilkan sebagai HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiah(mixed $value): string
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

function tanggal(?string $value, bool $includeTime = false): string
{
    if (!$value) {
        return '-';
    }

    return date($includeTime ? 'd M Y, H:i' : 'd M Y', strtotime($value));
}

function url(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

function go(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

function token(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(token()) . '">';
}

function verify_csrf(): void
{
    $submittedToken = (string) ($_POST['csrf'] ?? '');

    if (!hash_equals(token(), $submittedToken)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid. Muat ulang halaman, lalu coba kembali.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $message = $_SESSION['flash'];
    unset($_SESSION['flash']);

    echo '<div class="alert alert-' . e($message['type']) . '">';
    echo '<span>' . e($message['message']) . '</span>';
    echo '<button type="button" onclick="this.parentElement.remove()" aria-label="Tutup">×</button>';
    echo '</div>';
}

function signed_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function require_login(): void
{
    if (!signed_in()) {
        go('login');
    }
}

function me(): ?array
{
    if (!signed_in()) {
        return null;
    }

    return one('SELECT id, name, email, role, courier_id FROM users WHERE id = ?', [$_SESSION['user_id']]);
}

/** Guard di backend: bukan hanya menyembunyikan menu. */
function current_role(): string
{
    return me()['role'] ?? 'guest';
}

/** Aturan role satu sumber untuk seluruh halaman utama. */
function page_roles(): array
{
    return [
        'login'           => ['guest', 'admin', 'courier'],
        'tracking'        => ['guest', 'admin', 'courier'],
        'dashboard'       => ['admin', 'courier'],
        'profile'         => ['admin', 'courier'],
        'shipments'       => ['admin'],
        'shipment-form'   => ['admin'],
        'shipment'        => ['admin'],
        'customers'       => ['admin'],
        'couriers'        => ['admin'],
        'depots'          => ['admin'],
        'users'           => ['admin'],
        'reports'         => ['admin'],
        'export'          => ['admin'],
        'receipt'         => ['admin'],
        'courier-tasks'   => ['courier'],
        'courier-scan'    => ['courier'],
        'courier-history' => ['courier'],
    ];
}

function can_access_page(string $page, string $role): bool
{
    return in_array($role, page_roles()[$page] ?? [], true);
}

function require_role(string $role): void
{
    require_login();

    if (current_role() !== $role) {
        http_response_code(403);
        exit('403 - Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}

function courier_id(): int
{
    return current_role() === 'courier' ? (int) (me()['courier_id'] ?? 0) : 0;
}

/** Transisi status untuk kurir harus berurutan. */
function next_courier_status(string $current): ?string
{
    return [
        'pending' => 'picked_up',
        'picked_up' => 'in_transit',
        'in_transit' => 'delivered',
    ][$current] ?? null;
}

function one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetch() ?: null;
}

function many(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function count_rows(string $table): int
{
    $allowedTables = ['customers', 'couriers', 'shipments'];

    if (!in_array($table, $allowedTables, true)) {
        throw new InvalidArgumentException('Nama tabel tidak valid.');
    }

    return (int) db()->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
}

function status_options(): array
{
    return [
        'pending'    => 'Menunggu Pickup',
        'picked_up'  => 'Sudah Dijemput',
        'in_transit' => 'Dalam Perjalanan',
        'delivered'  => 'Terkirim',
        'cancelled'  => 'Dibatalkan',
    ];
}

function status_name(string $status): string
{
    return status_options()[$status] ?? ucfirst($status);
}

function badge_status(string $status): string
{
    $colors = [
        'pending'    => 'amber',
        'picked_up'  => 'blue',
        'in_transit' => 'purple',
        'delivered'  => 'green',
        'cancelled'  => 'gray',
    ];

    $color = $colors[$status] ?? 'gray';

    return '<span class="status status-' . e($color) . '"><i></i>'
        . e(status_name($status)) . '</span>';
}

function money_input(mixed $value): string
{
    return e(number_format((float) $value, 0, '.', ''));
}

/** Ambil data pengiriman dengan filter dari halaman pengiriman dan laporan. */
function filtered_shipments(
    string $query = '',
    string $status = '',
    string $from = '',
    string $to = '',
    int $limit = 0
): array {
    $conditions = [];
    $params = [];

    if ($query !== '') {
        $conditions[] = '(
            s.tracking_number LIKE ? OR
            s.recipient_name LIKE ? OR
            c.name LIKE ? OR
            s.destination_city LIKE ?
        )';
        $search = '%' . $query . '%';
        array_push($params, $search, $search, $search, $search);
    }

    if ($status !== '' && array_key_exists($status, status_options())) {
        $conditions[] = 's.status = ?';
        $params[] = $status;
    }

    if ($from !== '') {
        $conditions[] = 'DATE(s.created_at) >= ?';
        $params[] = $from;
    }

    if ($to !== '') {
        $conditions[] = 'DATE(s.created_at) <= ?';
        $params[] = $to;
    }

    $sql = '
        SELECT s.*, c.name AS customer_name, k.name AS courier_name
        FROM shipments s
        JOIN customers c ON s.customer_id = c.id
        LEFT JOIN couriers k ON s.courier_id = k.id
    ';

    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY s.created_at DESC, s.id DESC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . $limit;
    }

    return many($sql, $params);
}

function form_string(string $name, int $maxLength = 255): string
{
    return mb_substr(trim((string) ($_POST[$name] ?? '')), 0, $maxLength, 'UTF-8');
}

function is_valid_date(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date !== false && $date->format('Y-m-d') === $value;
}

function check_date_filters(string &$from, string &$to): void
{
    if ($from !== '' && !is_valid_date($from)) {
        $from = '';
    }

    if ($to !== '' && !is_valid_date($to)) {
        $to = '';
    }
}

function tracking_code(): string
{
    return 'KK' . date('ymd') . strtoupper(bin2hex(random_bytes(4)));
}
