<?php

/** Admin: akun login yang dipakai petugas admin dan kurir. */
function page_users(): void
{
    $accounts = many('
        SELECT u.id, u.name, u.email, u.role, u.courier_id, u.created_at,
               c.name AS courier_name
        FROM users u
        LEFT JOIN couriers c ON c.id = u.courier_id
        ORDER BY u.id DESC
    ');
    $availableCouriers = many('
        SELECT c.id, c.name FROM couriers c
        LEFT JOIN users u ON u.courier_id = c.id
        WHERE u.id IS NULL ORDER BY c.name ASC
    ');

    layout_start('users', 'Pengguna', 'Kelola akun login administrator dan kurir.');
    ?>
    <div class="page-actions">
        <span class="count-label"><?= count($accounts) ?> akun petugas</span>
        <a href="<?= url('couriers') ?>" class="btn btn-light"><?= ico('truck') ?> Kelola Data Kurir</a>
    </div>

    <div class="crud-grid">
        <section class="panel">
            <div class="panel-head"><div>
                <h2>Akun yang Terdaftar</h2>
                <p>Hanya admin dan kurir yang memerlukan akun. Pelanggan tidak perlu login.</p>
            </div></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Terhubung</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><b><?= e($account['name']) ?></b></td>
                            <td><?= e($account['email']) ?></td>
                            <td><span class="service-pill"><?= $account['role'] === 'admin' ? 'Admin' : 'Kurir' ?></span></td>
                            <td><?= e($account['courier_name'] ?: '-') ?></td>
                            <td>
                                <?php if ((int) $account['id'] !== (int) ($_SESSION['user_id'] ?? 0)): ?>
                                    <form action="index.php" method="post">
                                        <?php form_hidden('delete_user', 'users', (int) $account['id']); ?>
                                        <button type="submit" class="action-btn action-danger" title="Hapus akun" data-confirm="Yakin ingin menghapus akun petugas ini?"><?= ico('trash') ?></button>
                                    </form>
                                <?php else: ?>
                                    <small class="muted">Akun Anda</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="sub-panel">
                <h3>Atur Ulang Password Akun</h3>
                <form action="index.php" method="post" class="stack-fields">
                    <?php form_hidden('reset_user_password', 'users'); ?>
                    <div class="field"><label for="reset-user">Pilih Akun</label>
                        <select id="reset-user" name="id" required>
                            <option value="">Pilih akun</option>
                            <?php foreach ($accounts as $account): ?>
                                <option value="<?= (int) $account['id'] ?>"><?= e($account['name'] . ' (' . $account['email'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field"><label for="reset-password">Password Baru (min. 8 karakter)</label>
                        <input id="reset-password" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
                    <button class="btn btn-light" type="submit" data-confirm="Reset password akun terpilih?">Reset Password</button>
                </form>
            </div>
        </section>

        <section class="panel edit-panel">
            <h2>Buat Akun Petugas</h2>
            <p class="muted">Untuk kurir, daftarkan terlebih dahulu melalui menu Armada/Kurir.</p>
            <form action="index.php" method="post" class="stack-fields">
                <?php form_hidden('save_user', 'users'); ?>
                <div class="field"><label for="user-name">Nama Lengkap *</label>
                    <input id="user-name" name="name" maxlength="120" required placeholder="Nama petugas"></div>
                <div class="field"><label for="user-email">Email Login *</label>
                    <input id="user-email" type="email" name="email" maxlength="190" required placeholder="nama@kirimkita.id"></div>
                <div class="field"><label for="user-role">Jenis Akun *</label>
                    <select id="user-role" name="role" required data-user-role>
                        <option value="courier">Kurir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="field" data-courier-selector>
                    <label for="user-courier">Hubungkan dengan Data Kurir *</label>
                    <select id="user-courier" name="courier_id">
                        <option value="">Pilih kurir yang belum memiliki akun</option>
                        <?php foreach ($availableCouriers as $courier): ?>
                            <option value="<?= (int) $courier['id'] ?>"><?= e($courier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small>Hanya pengiriman milik kurir ini yang ditampilkan pada dashboard kurir.</small>
                </div>
                <div class="field"><label for="user-password">Password *</label>
                    <input id="user-password" type="password" name="password" minlength="8" required autocomplete="new-password" placeholder="Minimal 8 karakter"></div>
                <button type="submit" class="btn btn-primary btn-full"><?= ico('plus') ?> Tambah Akun</button>
            </form>
        </section>
    </div>
    <?php
    layout_end();
}
