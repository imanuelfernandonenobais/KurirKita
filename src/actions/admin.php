<?php

/** Aksi khusus administrator: pengiriman, pelanggan, armada, depo, akun. */

/** Simpan atau perbarui pelanggan. */
function save_customer(): never
{
    $id = max(0, (int) ($_POST['id'] ?? 0));
    $name = form_string('name', 120);
    $phone = form_string('phone', 30);
    $email = form_string('email', 190);
    $address = form_string('address', 500);

    if ($name === '' || $phone === '') {
        throw new DomainException('Nama dan nomor HP pelanggan wajib diisi.');
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new DomainException('Format email pelanggan tidak valid.');
    }

    if ($id > 0) {
        db()->prepare('UPDATE customers SET name=?, phone=?, email=?, address=? WHERE id=?')
            ->execute([$name, $phone, $email, $address, $id]);
    } else {
        db()->prepare('INSERT INTO customers (name, phone, email, address) VALUES (?, ?, ?, ?)')
            ->execute([$name, $phone, $email, $address]);
    }

    flash('success', $id ? 'Pelanggan berhasil diperbarui.' : 'Pelanggan berhasil ditambahkan.');
    go('customers');
}

function delete_customer(): never
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id < 1) {
        throw new DomainException('Pelanggan tidak ditemukan.');
    }

    $count = one('SELECT COUNT(*) AS total FROM shipments WHERE customer_id=?', [$id]);

    if ((int) ($count['total'] ?? 0) > 0) {
        throw new DomainException('Pelanggan ini memiliki pengiriman dan tidak dapat dihapus.');
    }

    db()->prepare('DELETE FROM customers WHERE id=?')->execute([$id]);
    flash('success', 'Pelanggan berhasil dihapus.');
    go('customers');
}

function save_courier(): never
{
    $id = max(0, (int) ($_POST['id'] ?? 0));
    $name = form_string('name', 120);
    $phone = form_string('phone', 30);
    $vehicle = form_string('vehicle', 80);

    if ($name === '' || $phone === '') {
        throw new DomainException('Nama dan nomor HP kurir wajib diisi.');
    }

    if ($id > 0) {
        db()->prepare('UPDATE couriers SET name=?, phone=?, vehicle=? WHERE id=?')
            ->execute([$name, $phone, $vehicle, $id]);
    } else {
        db()->prepare('INSERT INTO couriers (name, phone, vehicle) VALUES (?, ?, ?)')
            ->execute([$name, $phone, $vehicle]);
    }

    flash('success', $id ? 'Kurir berhasil diperbarui.' : 'Kurir berhasil ditambahkan.');
    go('couriers');
}

function delete_courier(): never
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id < 1) {
        throw new DomainException('Kurir tidak ditemukan.');
    }

    if (one('SELECT id FROM users WHERE courier_id=?', [$id])) {
        throw new DomainException('Hapus akun login kurir terlebih dahulu melalui menu Pengguna.');
    }

    $count = one('SELECT COUNT(*) AS total FROM shipments WHERE courier_id=?', [$id]);

    if ((int) ($count['total'] ?? 0) > 0) {
        throw new DomainException('Kurir ini memiliki riwayat pengiriman. Ubah kurir pengiriman terlebih dahulu.');
    }

    db()->prepare('DELETE FROM couriers WHERE id=?')->execute([$id]);
    flash('success', 'Kurir berhasil dihapus.');
    go('couriers');
}

function save_shipment(): never
{
    $id = max(0, (int) ($_POST['id'] ?? 0));
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $courierId = (int) ($_POST['courier_id'] ?? 0);
    $recipient = form_string('recipient_name', 120);
    $phone = form_string('recipient_phone', 30);
    $origin = form_string('origin_address', 500);
    $destination = form_string('destination_address', 500);
    $city = form_string('destination_city', 100);
    $notes = form_string('notes', 500);
    $service = form_string('service_type', 20);
    $weight = (float) ($_POST['weight_kg'] ?? 0);
    $cost = (float) ($_POST['shipping_cost'] ?? -1);

    if (!one('SELECT id FROM customers WHERE id=?', [$customerId])) {
        throw new DomainException('Pilih pelanggan yang valid.');
    }

    if ($courierId && !one('SELECT id FROM couriers WHERE id=?', [$courierId])) {
        throw new DomainException('Kurir tidak ditemukan.');
    }

    if ($recipient === '' || $phone === '' || $origin === '' || $destination === '' || $city === '') {
        throw new DomainException('Lengkapi nama/HP penerima, alamat asal, alamat tujuan, dan kota tujuan.');
    }

    if (!in_array($service, ['REG', 'EXPRESS'], true)) {
        throw new DomainException('Layanan tidak valid.');
    }

    if (!is_finite($weight) || $weight <= 0 || $weight > 100000) {
        throw new DomainException('Berat harus lebih dari 0 kg dan maksimal 100.000 kg.');
    }

    if (!is_finite($cost) || $cost < 0 || $cost > 1000000000) {
        throw new DomainException('Ongkir harus bernilai 0 hingga Rp 1.000.000.000.');
    }

    $values = [
        $customerId, $courierId ?: null, $recipient, $phone, $origin,
        $destination, $city, $service, $weight, $cost,
    ];

    if ($id > 0) {
        if (!one('SELECT id FROM shipments WHERE id=?', [$id])) {
            throw new DomainException('Pengiriman tidak ditemukan.');
        }

        $sql = '
            UPDATE shipments SET
                customer_id=?, courier_id=?, recipient_name=?, recipient_phone=?,
                origin_address=?, destination_address=?, destination_city=?,
                service_type=?, weight_kg=?, shipping_cost=?, notes=?
            WHERE id=?
        ';

        db()->prepare($sql)->execute([...$values, $notes, $id]);
        flash('success', 'Data pengiriman berhasil diperbarui.');
        go('shipment', ['id' => $id]);
    }

    db()->beginTransaction();

    try {
        $tracking = tracking_code();
        $sql = '
            INSERT INTO shipments (
                tracking_number, customer_id, courier_id, recipient_name,
                recipient_phone, origin_address, destination_address,
                destination_city, service_type, weight_kg, shipping_cost,
                status, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ';

        db()->prepare($sql)->execute([$tracking, ...$values, 'pending', $notes]);
        $id = (int) db()->lastInsertId();
        db()->prepare('INSERT INTO tracking_events (shipment_id, status, description) VALUES (?, ?, ?)')
            ->execute([$id, 'pending', 'Pesanan dibuat dan menunggu penjemputan.']);
        db()->commit();
    } catch (Throwable $exception) {
        db()->rollBack();
        throw $exception;
    }

    flash('success', 'Pengiriman berhasil dibuat. Nomor resi: ' . $tracking);
    go('shipment', ['id' => $id]);
}

function update_shipment_status(): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $status = form_string('status', 30);
    $description = form_string('description', 500);

    if (!array_key_exists($status, status_options())) {
        throw new DomainException('Status tidak valid.');
    }

    $shipment = one('SELECT id FROM shipments WHERE id=?', [$id]);

    if (!$shipment) {
        throw new DomainException('Pengiriman tidak ditemukan.');
    }

    if ($description === '') {
        $description = 'Status pengiriman berubah menjadi ' . status_name($status) . '.';
    }

    db()->beginTransaction();

    try {
        db()->prepare('UPDATE shipments SET status=? WHERE id=?')->execute([$status, $id]);
        db()->prepare('INSERT INTO tracking_events (shipment_id, status, description) VALUES (?, ?, ?)')
            ->execute([$id, $status, $description]);
        db()->commit();
    } catch (Throwable $exception) {
        db()->rollBack();
        throw $exception;
    }

    flash('success', 'Status dan riwayat pelacakan berhasil diperbarui.');
    go('shipment', ['id' => $id]);
}

function delete_shipment(): never
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id < 1) {
        throw new DomainException('Pengiriman tidak ditemukan.');
    }

    db()->prepare('DELETE FROM shipments WHERE id=?')->execute([$id]);
    flash('success', 'Pengiriman berhasil dihapus.');
    go('shipments');
}

/** Admin menyiapkan akun admin atau kurir, bukan akun pelacak publik. */
function save_user(): never
{
    $name = form_string('name', 120);
    $email = strtolower(form_string('email', 190));
    $password = (string) ($_POST['password'] ?? '');
    $role = form_string('role', 20);
    $courierId = (int) ($_POST['courier_id'] ?? 0);

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new DomainException('Nama dan email harus valid.');
    }

    if (strlen($password) < 8) {
        throw new DomainException('Password minimal delapan karakter.');
    }

    if (!in_array($role, ['admin', 'courier'], true)) {
        throw new DomainException('Role akun tidak valid.');
    }

    if ($role === 'courier') {
        if ($courierId < 1 || !one('SELECT id FROM couriers WHERE id=?', [$courierId])) {
            throw new DomainException('Pilih kurir yang terdaftar.');
        }
        if (one('SELECT id FROM users WHERE courier_id=?', [$courierId])) {
            throw new DomainException('Kurir tersebut sudah memiliki akun.');
        }
    } else {
        $courierId = 0;
    }

    if (one('SELECT id FROM users WHERE email=?', [$email])) {
        throw new DomainException('Email tersebut sudah terdaftar.');
    }

    db()->prepare('INSERT INTO users (name, email, password_hash, role, courier_id) VALUES (?, ?, ?, ?, ?)')
        ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $courierId ?: null]);

    flash('success', 'Akun ' . ($role === 'admin' ? 'admin' : 'kurir') . ' berhasil dibuat.');
    go('users');
}

function reset_user_password(): never
{
    $id = (int) ($_POST['id'] ?? 0);
    $password = (string) ($_POST['password'] ?? '');

    if ($id < 1 || strlen($password) < 8) {
        throw new DomainException('Pilih akun dan gunakan password minimal delapan karakter.');
    }

    db()->prepare('UPDATE users SET password_hash=? WHERE id=?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);

    flash('success', 'Password akun berhasil direset.');
    go('users');
}

function delete_user(): never
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id < 1 || $id === (int) $_SESSION['user_id']) {
        throw new DomainException('Akun sendiri tidak bisa dihapus.');
    }

    db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    flash('success', 'Akun berhasil dihapus.');
    go('users');
}

function save_depot(): never
{
    $id = max(0, (int) ($_POST['id'] ?? 0));
    $name = form_string('name', 120);
    $city = form_string('city', 100);
    $address = form_string('address', 500);
    $phone = form_string('phone', 30);

    if ($name === '' || $city === '' || $address === '') {
        throw new DomainException('Nama, kota, dan alamat depo wajib diisi.');
    }

    if ($id > 0) {
        db()->prepare('UPDATE depots SET name=?, city=?, address=?, phone=? WHERE id=?')
            ->execute([$name, $city, $address, $phone, $id]);
    } else {
        db()->prepare('INSERT INTO depots (name, city, address, phone) VALUES (?, ?, ?, ?)')
            ->execute([$name, $city, $address, $phone]);
    }

    flash('success', 'Data depo berhasil disimpan.');
    go('depots');
}

function delete_depot(): never
{
    $id = (int) ($_POST['id'] ?? 0);

    if ($id < 1) {
        throw new DomainException('Depo tidak ditemukan.');
    }

    db()->prepare('DELETE FROM depots WHERE id=?')->execute([$id]);
    flash('success', 'Depo berhasil dihapus.');
    go('depots');
}
