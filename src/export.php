<?php

/** Export laporan dalam CSV UTF-8 yang kompatibel dengan Excel. */

require_role('admin');

$query = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$from = (string) ($_GET['from'] ?? '');
$to = (string) ($_GET['to'] ?? '');
check_date_filters($from, $to);

$shipments = filtered_shipments($query, $status, $from, $to);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="laporan-kirimkita-' . date('Ymd-His') . '.csv"');

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, [
    'No Resi', 'Tanggal', 'Pengirim', 'Penerima', 'Kota Tujuan',
    'Layanan', 'Kurir', 'Berat (kg)', 'Ongkir (Rp)', 'Status',
]);

// Cegah CSV formula injection pada data teks dari pengguna.
$escapeCsv = static function (mixed $value): string {
    $text = (string) $value;

    return preg_match('/^\s*[=+@\-]/u', $text) ? "'" . $text : $text;
};

foreach ($shipments as $item) {
    fputcsv($output, [
        $escapeCsv($item['tracking_number']),
        $item['created_at'],
        $escapeCsv($item['customer_name']),
        $escapeCsv($item['recipient_name']),
        $escapeCsv($item['destination_city']),
        $escapeCsv($item['service_type']),
        $escapeCsv($item['courier_name'] ?? ''),
        $item['weight_kg'],
        $item['shipping_cost'],
        status_name($item['status']),
    ]);
}

fclose($output);
