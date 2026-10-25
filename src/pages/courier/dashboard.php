<?php

/** Kurir: ringkasan hanya pengiriman yang ditugaskan kepada akun tersebut. */
function page_courier_dashboard(): void
{
    $courierId = courier_id();
    $totals = one('
        SELECT COUNT(*) AS total,
               COALESCE(SUM(status = "pending"), 0) AS pending,
               COALESCE(SUM(status IN ("picked_up", "in_transit")), 0) AS active,
               COALESCE(SUM(status = "delivered"), 0) AS delivered
        FROM shipments WHERE courier_id = ?
    ', [$courierId]) ?? [];

    $tasks = many('
        SELECT s.id, s.tracking_number, s.recipient_name, s.destination_city,
               s.status, s.created_at
        FROM shipments s
        WHERE s.courier_id = ? AND s.status IN ("pending", "picked_up", "in_transit")
        ORDER BY s.created_at DESC, s.id DESC LIMIT 7
    ', [$courierId]);

    layout_start('dashboard', 'Dashboard Kurir', 'Ringkasan tugas pengiriman yang ditugaskan kepada Anda.');
    ?>
    <div class="page-actions">
        <span class="date-pill"><?= e(date('d M Y')) ?></span>
        <a href="<?= url('courier-scan') ?>" class="btn btn-primary"><?= ico('search') ?> Cari / Scan Resi</a>
    </div>
    <div class="stats-grid">
        <?php stats_card('Total Penugasan', (string) ($totals['total'] ?? 0), 'box', 'red', 'Paket milik Anda'); ?>
        <?php stats_card('Menunggu Pickup', (string) ($totals['pending'] ?? 0), 'truck', 'orange', 'Siap dijemput'); ?>
        <?php stats_card('Dalam Proses', (string) ($totals['active'] ?? 0), 'pin', 'blue', 'Sedang dikerjakan'); ?>
        <?php stats_card('Sudah Terkirim', (string) ($totals['delivered'] ?? 0), 'check', 'green', 'Pengiriman selesai'); ?>
    </div>

    <div class="dashboard-grids">
        <section class="panel">
            <div class="panel-head"><div><h2>Tugas yang Perlu Diselesaikan</h2><p>Daftar pengiriman aktif untuk Anda</p></div>
                <a href="<?= url('courier-tasks') ?>" class="text-link">Lihat Semua <?= ico('arrow') ?></a>
            </div>
            <?php courier_task_table($tasks, false); ?>
        </section>
        <section class="panel quick-panel">
            <div class="panel-head"><div><h2>Akses Cepat</h2><p>Kelola aktivitas pengiriman Anda</p></div></div>
            <div class="quick-links">
                <a href="<?= url('courier-tasks') ?>"><span class="quick-ico quick-blue"><?= ico('truck') ?></span><span><b>Tugas Pengiriman</b><small>Lihat dan perbarui status paket</small></span><?= ico('arrow') ?></a>
                <a href="<?= url('courier-scan') ?>"><span class="quick-ico quick-red"><?= ico('search') ?></span><span><b>Cari Nomor Resi</b><small>Input resi atau gunakan scanner eksternal</small></span><?= ico('arrow') ?></a>
                <a href="<?= url('courier-history') ?>"><span class="quick-ico quick-green"><?= ico('history') ?></span><span><b>Riwayat Selesai</b><small>Pengiriman yang sudah diterima</small></span><?= ico('arrow') ?></a>
                <a href="<?= url('profile') ?>"><span class="quick-ico quick-orange"><?= ico('user') ?></span><span><b>Profil Kurir</b><small>Perbarui akun dan password</small></span><?= ico('arrow') ?></a>
            </div>
        </section>
    </div>
    <?php
    layout_end();
}
