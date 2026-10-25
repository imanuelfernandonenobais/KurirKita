<?php

/** Aksi kurir dengan verifikasi kepemilikan paket di sisi server. */

/** Kurir hanya boleh memperbarui paket miliknya dan ke status berikutnya. */
function courier_update_status(): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $assignedCourier = courier_id();

    if ($id < 1 || $assignedCourier < 1) {
        throw new DomainException('Akun kurir atau pengiriman tidak valid.');
    }

    db()->beginTransaction();

    try {
        $query = db()->prepare('SELECT id, status FROM shipments WHERE id=? AND courier_id=? FOR UPDATE');
        $query->execute([$id, $assignedCourier]);
        $shipment = $query->fetch();

        if (!$shipment) {
            throw new DomainException('Paket tidak ditemukan atau bukan tugas Anda.');
        }

        $next = next_courier_status($shipment['status']);

        if (!$next) {
            throw new DomainException('Status paket sudah final dan tidak bisa diperbarui lagi.');
        }

        $submitted = form_string('status', 30);

        if ($submitted !== $next) {
            throw new DomainException('Perubahan status harus mengikuti urutan proses pengiriman.');
        }

        $description = form_string('description', 500);

        if ($description === '') {
            $description = 'Kurir memperbarui paket menjadi ' . status_name($next) . '.';
        }

        db()->prepare('UPDATE shipments SET status=? WHERE id=?')->execute([$next, $id]);
        db()->prepare('INSERT INTO tracking_events (shipment_id, status, description) VALUES (?, ?, ?)')
            ->execute([$id, $next, $description]);
        db()->commit();
    } catch (Throwable $error) {
        db()->rollBack();
        throw $error;
    }

    flash('success', 'Status paket berhasil diperbarui.');
    go('courier-tasks');
}
