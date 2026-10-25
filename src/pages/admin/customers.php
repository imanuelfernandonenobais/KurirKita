<?php

/** KirimKita — halaman customers. */

function page_customers(): void {
    $edit = max(0,(int)($_GET['edit']??0));
    $item = $edit ? one('SELECT * FROM customers WHERE id=?',[$edit]) : null;
    $items = many('SELECT c.*, (SELECT COUNT(*) FROM shipments s WHERE s.customer_id=c.id) AS shipments_count FROM customers c ORDER BY c.id DESC');
    layout_start('customers','Data Pelanggan','Tambah, ubah, dan kelola pelanggan pengirim paket.'); ?>
<div class="page-actions">
  <span class="count-label"><?=count($items)?> pelanggan terdaftar</span>
  <a class="btn btn-light" href="<?=url('customers')?>"><?=ico('plus')?> Tambah Baru</a>
</div>
<div class="crud-grid">
  <section class="panel">
    <div class="panel-head">
      <div>
        <h2>Daftar Pelanggan</h2>
        <p>Data pengirim yang tersimpan dalam sistem</p>
      </div>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Pelanggan</th>
            <th>Kontak</th>
            <th>Alamat</th>
            <th>Pengiriman</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $d): ?>
          <tr>
            <td>
              <b><?=e($d['name'])?>
              </b>
              <small class="table-sub">#PLG<?=str_pad((string)$d['id'],4,'0',STR_PAD_LEFT)?>
              </small>
            </td>
            <td><?=e($d['phone'])?>
              <small class="table-sub"><?=e($d['email'] ?: 'Tanpa email')?>
              </small>
            </td>
            <td class="wrap-address"><?=e($d['address'] ?: '-')?>
            </td>
            <td>
              <span class="counter-pill"><?=$d['shipments_count']?>
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <a title="Edit" class="action-btn" href="<?=url('customers',['edit'=>$d['id']])?>"><?=ico('edit')?>
                </a>
                <form method="post" action="index.php">
                  <?php form_hidden('delete_customer','customers',(int)$d['id']); ?>
                  <button class="action-btn action-danger" title="Hapus" type="submit" data-confirm="Hapus pelanggan ini? Pelanggan dengan pengiriman tidak bisa dihapus."><?=ico('trash')?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(!$items): ?>
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <b>Belum ada pelanggan</b>
                <p>Tambahkan pelanggan lewat formulir di sebelah kanan.</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <section class="panel edit-panel">
    <h2><?=$item?'Edit Pelanggan':'Tambah Pelanggan'?>
    </h2>
    <p class="muted">Isi data dasar pelanggan pengirim.</p>
    <form action="index.php" method="post" class="stack-fields">
      <?php form_hidden('save_customer','customers',(int)($item['id']??0)); ?>
      <div class="field">
        <label>Nama Pelanggan <em>*</em>
        </label>
        <input name="name" required maxlength="120" value="<?=e($item['name']??'')?>" placeholder="Nama lengkap">
      </div>
      <div class="field">
        <label>No. Telepon <em>*</em>
        </label>
        <input name="phone" required maxlength="30" value="<?=e($item['phone']??'')?>" placeholder="08xxxxxxxxxx">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" maxlength="190" value="<?=e($item['email']??'')?>" placeholder="nama@email.com">
      </div>
      <div class="field">
        <label>Alamat</label>
        <textarea name="address" maxlength="500" rows="3" placeholder="Alamat lengkap pelanggan"><?=e($item['address']??'')?>
        </textarea>
      </div>
      <button class="btn btn-primary btn-full" type="submit"><?=ico('check')?> <?=$item?'Simpan Perubahan':'Tambah Pelanggan'?>
      </button>
      <?php if($item): ?>
      <a class="btn btn-light btn-full" href="<?=url('customers')?>">Batal Edit</a>
      <?php endif; ?>
    </form>
  </section>
</div>
<?php layout_end(); }
