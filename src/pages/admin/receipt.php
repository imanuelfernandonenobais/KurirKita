<?php

/** SVG barcode Code39 lokal; label tetap bisa discan tanpa CDN/internet. */
function shipment_barcode(string $number): string
{
    $code = strtoupper(trim($number));
    if (!preg_match('/^[A-Z0-9]{1,32}$/', $code)) {
        return '';
    }

    $patterns = [
        '0' => 'bsbSBsBsb',
        '1' => 'BsbSbsbsB',
        '2' => 'bsBSbsbsB',
        '3' => 'BsBSbsbsb',
        '4' => 'bsbSBsbsB',
        '5' => 'BsbSBsbsb',
        '6' => 'bsBSBsbsb',
        '7' => 'bsbSbsBsB',
        '8' => 'BsbSbsBsb',
        '9' => 'bsBSbsBsb',
        'A' => 'BsbsbSbsB',
        'B' => 'bsBsbSbsB',
        'C' => 'BsBsbSbsb',
        'D' => 'bsbsBSbsB',
        'E' => 'BsbsBSbsb',
        'F' => 'bsBsBSbsb',
        'G' => 'bsbsbSBsB',
        'H' => 'BsbsbSBsb',
        'I' => 'bsBsbSBsb',
        'J' => 'bsbsBSBsb',
        'K' => 'BsbsbsbSB',
        'L' => 'bsBsbsbSB',
        'M' => 'BsBsbsbSb',
        'N' => 'bsbsBsbSB',
        'O' => 'BsbsBsbSb',
        'P' => 'bsBsBsbSb',
        'Q' => 'bsbsbsBSB',
        'R' => 'BsbsbsBSb',
        'S' => 'bsBsbsBSb',
        'T' => 'bsbsBsBSb',
        'U' => 'BSbsbsbsB',
        'V' => 'bSBsbsbsB',
        'W' => 'BSBsbsbsb',
        'X' => 'bSbsBsbsB',
        'Y' => 'BSbsBsbsb',
        'Z' => 'bSBsBsbsb',
        '*' => 'bSbsBsBsb',
    ];
    $x = 12;
    $bars = '';
    foreach (str_split('*' . $code . '*') as $character) {
        $pattern = $patterns[$character];
        foreach (str_split($pattern) as $part) {
            $width = ctype_upper($part) ? 3 : 1;
            if ($part === 'B' || $part === 'b') {
                $bars .= '<rect x="' . $x . '" y="1" width="' . $width . '" height="73" />';
            }
            $x += $width;
        }
        $x += 1;
    }
    $total = $x + 12;
    return '<svg class="receipt-barcode" role="img" aria-label="Barcode resi '
        . e($number) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '
        . $total . ' 76" fill="#111">' . $bars . '</svg>';
}

/** Label cetak pengiriman. Hanya admin, diperiksa sebelum halaman dimuat. */
function page_receipt(): void
{
    $id = max(0, (int) ($_GET['id'] ?? 0));
    $shipment = one('
        SELECT s.*, c.name AS customer_name, c.phone AS customer_phone,
               k.name AS courier_name
        FROM shipments s
        JOIN customers c ON c.id = s.customer_id
        LEFT JOIN couriers k ON k.id = s.courier_id
        WHERE s.id = ?
    ', [$id]);

    if (!$shipment) {
        http_response_code(404);
        echo 'Pengiriman tidak ditemukan.';
        return;
    }

    top_html('Cetak Resi');
    ?>
    <main class="receipt-page">
        <div class="receipt-toolbar">
            <a class="btn btn-light" href="<?= url('shipment', ['id' => $id]) ?>">← Kembali ke Detail</a>
            <button class="btn btn-primary" type="button" data-print>🖨 Cetak Resi</button>
        </div>
        <section class="receipt-paper" aria-label="Label pengiriman KirimKita">
            <header class="receipt-head">
                <div class="receipt-brand"><span class="brand-icon">KK</span><span><strong>KirimKita</strong><small>LOGISTICS PLATFORM</small></span></div>
                <div><strong><?= e($shipment['service_type']) ?></strong><small><?= e(tanggal($shipment['created_at'], true)) ?></small></div>
            </header>
            <div class="receipt-code">
                <span>NOMOR RESI</span>
                <h1><?= e($shipment['tracking_number']) ?></h1>
                <div class="receipt-barcode-wrap"><?= shipment_barcode($shipment['tracking_number']) ?></div>
                <p><?= e(status_name($shipment['status'])) ?></p>
            </div>
            <div class="receipt-grid">
                <div>
                    <h2>PENGIRIM</h2>
                    <strong><?= e($shipment['customer_name']) ?></strong>
                    <p><?= e($shipment['customer_phone']) ?></p>
                    <p><?= nl2br(e($shipment['origin_address'])) ?></p>
                </div>
                <div>
                    <h2>PENERIMA</h2>
                    <strong><?= e($shipment['recipient_name']) ?></strong>
                    <p><?= e($shipment['recipient_phone']) ?></p>
                    <p><?= nl2br(e($shipment['destination_address'])) ?></p>
                    <strong><?= e($shipment['destination_city']) ?></strong>
                </div>
            </div>
            <div class="receipt-foot">
                <p><b>Berat:</b> <?= e($shipment['weight_kg']) ?> kg</p>
                <p><b>Ongkir:</b> <?= rupiah($shipment['shipping_cost']) ?></p>
                <p><b>Kurir:</b> <?= e($shipment['courier_name'] ?: 'Belum ditugaskan') ?></p>
            </div>
            <div class="receipt-note">Simpan nomor resi untuk melacak status paket melalui KirimKita. Label ini dibuat oleh sistem KirimKita.</div>
        </section>
    </main>
    <?php
    end_html();
}
