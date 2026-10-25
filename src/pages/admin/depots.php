<?php

/** Admin: pengelolaan gudang / depo (CRUD). */
function page_depots(): void
{
    $editId = max(0, (int) ($_GET['edit'] ?? 0));
    $editing = $editId ? one('SELECT * FROM depots WHERE id = ?', [$editId]) : null;
    $depots = many('SELECT * FROM depots ORDER BY id DESC');

    layout_start('depots', 'Data Depo', 'Kelola lokasi gudang dan pusat operasional.');
    ?>
    <div class="page-actions">
        <span class="count-label"><?= count($depots) ?> depo terdaftar</span>
        <a class="btn btn-light" href="<?= url('depots') ?>"><?= ico('plus') ?> Tambah Depo</a>
    </div>

    <div class="crud-grid">
        <section class="panel">
            <div class="panel-head"><div>
                <h2>Daftar Depo</h2>
                <p>Data lokasi cabang dan gudang KirimKita</p>
            </div></div>
            <div class="table-scroll">
                <table class="data-table">
                    <thead><tr><th>Nama Depo</th><th>Kota</th><th>Alamat</th><th>Kontak</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($depots as $depot): ?>
                        <tr>
                            <td><b><?= e($depot['name']) ?></b></td>
                            <td><?= e($depot['city']) ?></td>
                            <td><?= e($depot['address']) ?></td>
                            <td><?= e($depot['phone'] ?: '-') ?></td>
                            <td><div class="action-buttons">
                                <a class="action-btn" title="Edit" href="<?= url('depots', ['edit' => $depot['id']]) ?>"><?= ico('edit') ?></a>
                                <form action="index.php" method="post">
                                    <?php form_hidden('delete_depot', 'depots', (int) $depot['id']); ?>
                                    <button class="action-btn action-danger" type="submit" title="Hapus" data-confirm="Hapus data depo ini?"><?= ico('trash') ?></button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$depots): ?>
                        <tr><td colspan="5" class="muted">Belum ada depo. Tambahkan melalui formulir.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel edit-panel">
            <h2><?= $editing ? 'Edit Depo' : 'Tambah Depo' ?></h2>
            <p class="muted">Isi informasi cabang secara lengkap.</p>
            <form action="index.php" method="post" class="stack-fields">
                <?php form_hidden('save_depot', 'depots', (int) ($editing['id'] ?? 0)); ?>
                <div class="field"><label for="depot-name">Nama Depo *</label>
                    <input id="depot-name" name="name" maxlength="120" required value="<?= e($editing['name'] ?? '') ?>" placeholder="Contoh: Hub Bandung"></div>
                <div class="field"><label for="depot-city">Kota *</label>
                    <input id="depot-city" name="city" maxlength="100" required value="<?= e($editing['city'] ?? '') ?>" placeholder="Bandung"></div>
                <div class="field"><label for="depot-address">Alamat *</label>
                    <textarea id="depot-address" name="address" rows="3" maxlength="500" required><?= e($editing['address'] ?? '') ?></textarea></div>
                <div class="field"><label for="depot-phone">Telepon</label>
                    <input id="depot-phone" name="phone" maxlength="30" value="<?= e($editing['phone'] ?? '') ?>" placeholder="022xxxxxxxx"></div>
                <button type="submit" class="btn btn-primary btn-full"><?= ico('check') ?> Simpan Depo</button>
                <?php if ($editing): ?>
                    <a class="btn btn-light btn-full" href="<?= url('depots') ?>">Batal Edit</a>
                <?php endif; ?>
            </form>
        </section>
    </div>
    <?php
    layout_end();
}
