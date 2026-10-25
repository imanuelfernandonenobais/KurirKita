<?php

/** Tabel tugas hanya menerima data paket dari query dengan courier_id yang terikat. */
function courier_task_table(array $rows, bool $includeActions = true): void
{
    ?>
    <div class="table-scroll">
        <table class="data-table">
            <thead><tr><th>No. Resi</th><th>Penerima</th><th>Kota Tujuan</th><th>Status</th><th>Langkah Selanjutnya</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $task): ?>
                <?php $next = next_courier_status($task['status']); ?>
                <tr>
                    <td><b class="mono"><?= e($task['tracking_number']) ?></b><small class="table-sub"><?= e(tanggal($task['created_at'])) ?></small></td>
                    <td><?= e($task['recipient_name']) ?></td>
                    <td><?= e($task['destination_city']) ?></td>
                    <td><?= badge_status($task['status']) ?></td>
                    <td>
                        <?php if ($next && $includeActions): ?>
                            <form action="index.php" method="post" class="courier-action">
                                <?php form_hidden('courier_update_status', 'courier-tasks', (int) $task['id']); ?>
                                <input type="hidden" name="status" value="<?= e($next) ?>">
                                <input class="note-input" name="description" maxlength="500" aria-label="Catatan untuk <?= e($task['tracking_number']) ?>" placeholder="Catatan (opsional)">
                                <button class="btn btn-primary btn-sm" type="submit" data-confirm="Perbarui status paket <?= e($task['tracking_number']) ?> menjadi <?= e(status_name($next)) ?>?">
                                    <?= ico('check') ?> <?= e(status_name($next)) ?>
                                </button>
                            </form>
                        <?php elseif ($next): ?>
                            <a class="text-link" href="<?= url('courier-tasks') ?>">Perbarui di Tugas <?= ico('arrow') ?></a>
                        <?php else: ?>
                            <span class="muted">Selesai</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="5"><div class="empty-state"><b>Belum ada pengiriman</b><p>Tugas baru akan muncul ketika admin menugaskan paket kepada Anda.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function page_courier_tasks(): void
{
    $courierId = courier_id();
    $query = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
    $params = [$courierId];
    $sql = '
        SELECT s.id, s.tracking_number, s.recipient_name, s.destination_city,
               s.status, s.created_at
        FROM shipments s
        WHERE s.courier_id = ? AND s.status IN ("pending", "picked_up", "in_transit")
    ';

    if ($query !== '') {
        $sql .= ' AND (s.tracking_number LIKE ? OR s.recipient_name LIKE ?)';
        array_push($params, '%' . $query . '%', '%' . $query . '%');
    }

    $sql .= ' ORDER BY s.created_at DESC, s.id DESC';
    $tasks = many($sql, $params);

    layout_start('courier-tasks', 'Tugas Pengiriman', 'Anda hanya dapat memproses paket yang ditugaskan kepada akun Anda.');
    ?>
    <section class="panel">
        <form action="index.php" method="get" class="filter-bar">
            <input type="hidden" name="page" value="courier-tasks">
            <div class="search-field"><?= ico('search') ?>
                <input name="q" value="<?= e($query) ?>" placeholder="Cari resi atau penerima..."></div>
            <button type="submit" class="btn btn-dark">Cari</button>
            <a href="<?= url('courier-tasks') ?>" class="btn btn-light">Reset</a>
        </form>
        <?php courier_task_table($tasks); ?>
    </section>
    <section class="panel">
        <h2>Urutan Pembaruan Status</h2>
        <p>Kurir harus memperbarui status secara berurutan: <b>Menunggu Pickup</b> → <b>Sudah Dijemput</b> → <b>Dalam Perjalanan</b> → <b>Terkirim</b>. Paket yang dibatalkan tidak dapat diperbarui.</p>
    </section>
    <?php
    layout_end();
}
