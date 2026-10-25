<?php

/** Riwayat pengiriman selesai yang pernah ditugaskan kepada kurir. */
function page_courier_history(): void
{
    $rows = many('
        SELECT s.id, s.tracking_number, s.recipient_name, s.destination_city,
               s.status, s.updated_at AS created_at
        FROM shipments s
        WHERE s.courier_id = ? AND s.status IN ("delivered", "cancelled")
        ORDER BY s.updated_at DESC, s.id DESC
    ', [courier_id()]);

    layout_start('courier-history', 'Riwayat Pengiriman', 'Daftar paket selesai atau dibatalkan yang pernah ditugaskan kepada Anda.');
    ?>
    <section class="panel">
        <div class="panel-head"><div><h2>Riwayat Paket</h2><p><?= count($rows) ?> pengiriman dengan status akhir</p></div></div>
        <?php courier_task_table($rows); ?>
    </section>
    <?php
    layout_end();
}
