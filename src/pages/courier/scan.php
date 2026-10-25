<?php

/**
 * Pencarian resi untuk kurir. Bekerja dengan ketikan manual, pemindai
 * barcode USB/Bluetooth (keyboard input), tanpa dependensi eksternal.
 */
function page_courier_scan(): void
{
    $resi = strtoupper(mb_substr(trim((string) ($_GET['resi'] ?? '')), 0, 40));
    $shipment = $resi !== ''
        ? one('
            SELECT id, tracking_number, recipient_name, destination_city, status,
                   destination_address, recipient_phone
            FROM shipments WHERE tracking_number = ? AND courier_id = ?
        ', [$resi, courier_id()])
        : null;

    layout_start('courier-scan', 'Cari / Scan Paket', 'Cari paket dengan nomor resi, atau gunakan scanner barcode sebagai input.');
    ?>
    <section class="panel courier-lookup">
        <div class="panel-head"><div><h2>Periksa Nomor Resi</h2><p>Hanya paket yang ditugaskan kepada Anda dapat ditemukan.</p></div></div>
        <form action="index.php" method="get" class="tracking-search">
            <input type="hidden" name="page" value="courier-scan">
            <div><?= ico('search') ?><input name="resi" autofocus maxlength="40" required value="<?= e($resi) ?>" placeholder="Ketik / pindai nomor resi"></div>
            <button type="submit" class="btn btn-primary">Cari Paket</button>
        </form>
        <div data-camera-scanner>
            <div class="camera-actions">
                <button class="btn btn-light btn-sm" type="button" data-camera-start>📷 Scan menggunakan kamera</button>
                <button class="btn btn-light btn-sm" type="button" data-camera-stop hidden>Matikan kamera</button>
                <span class="camera-feedback" data-camera-message role="status" aria-live="polite">Bisa juga mengetik resi atau menggunakan scanner USB/Bluetooth.</span>
            </div>
            <div class="camera-preview" data-camera-preview hidden>
                <video data-camera-video muted playsinline aria-label="Tampilan kamera untuk membaca barcode paket"></video>
            </div>
        </div>
    </section>

    <?php if ($resi !== ''): ?>
        <?php if (!$shipment): ?>
            <section class="panel notfound"><h2>Resi tidak ditemukan dalam tugas Anda</h2>
                <p>Pastikan resi benar dan admin sudah menugaskan paket ini kepada Anda.</p></section>
        <?php else: ?>
            <?php $next = next_courier_status($shipment['status']); ?>
            <section class="panel">
                <div class="panel-head"><div><p class="eyebrow">PAKET DITEMUKAN</p>
                    <h2 class="mono"><?= e($shipment['tracking_number']) ?></h2></div>
                    <?= badge_status($shipment['status']) ?>
                </div>
                <div class="info-grid">
                    <div><span>Nama Penerima</span><b><?= e($shipment['recipient_name']) ?></b></div>
                    <div><span>Kontak Penerima</span><b><?= e($shipment['recipient_phone']) ?></b></div>
                    <div><span>Kota Tujuan</span><b><?= e($shipment['destination_city']) ?></b></div>
                    <div><span>Alamat Pengiriman</span><b><?= e($shipment['destination_address']) ?></b></div>
                </div>
                <?php if ($next): ?>
                    <form action="index.php" method="post" class="stack-fields courier-lookup-update">
                        <?php form_hidden('courier_update_status', 'courier-tasks', (int) $shipment['id']); ?>
                        <input type="hidden" name="status" value="<?= e($next) ?>">
                        <div class="field"><label for="scan-note">Catatan Perjalanan (opsional)</label>
                            <textarea id="scan-note" name="description" maxlength="500" rows="2" placeholder="Contoh: Paket telah diterima oleh penerima."></textarea></div>
                        <button class="btn btn-primary" type="submit" data-confirm="Lanjutkan status menjadi <?= e(status_name($next)) ?>?">
                            <?= ico('check') ?> Tandai <?= e(status_name($next)) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <p class="muted">Pengiriman ini sudah selesai atau dibatalkan.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>
    <?php
    layout_end();
}
