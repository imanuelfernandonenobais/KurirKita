<?php

/** KirimKita — halaman couriers. */

function page_couriers(): void {
    $edit = max(0,(int)($_GET['edit']??0));
    $item = $edit ? one('SELECT * FROM couriers WHERE id=?',[$edit]) : null;
    $items = many('SELECT c.*, u.email AS login_email, (SELECT COUNT(*) FROM shipments s WHERE s.courier_id=c.id) AS shipments_count FROM couriers c LEFT JOIN users u ON u.courier_id = c.id ORDER BY c.id DESC');
    layout_start('couriers','Data Kurir','Kelola mitra kurir yang bertugas mengirimkan paket.'); ?>
<div class="page-actions">
  <span class="count-label"><?=count($items)?> kurir terdaftar</span>
  <a class="btn btn-light" href="<?=url('couriers')?>"><?=ico('plus')?> Tambah Baru</a>
</div>
<div class="crud-grid">
  <section class="panel">
    <div class="panel-head">
      <div>
        <h2>Daftar Kurir</h2>
        <p>Kurir yang tersedia untuk penugasan</p>
      </div>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama Kurir</th>
            <th>Telepon</th>
            <th>Akun Login</th>
            <th>Kendaraan</th>
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
              <small class="table-sub">#KUR<?=str_pad((string)$d['id'],4,'0',STR_PAD_LEFT)?>
              </small>
            </td>
            <td><?=e($d['phone'])?>
            </td>
            <td><?=e($d['login_email'] ?: 'Belum dibuat')?>
            </td>
            <td><?=e($d['vehicle'] ?: '-')?>
            </td>
            <td>
              <span class="counter-pill"><?=$d['shipments_count']?>
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <a title="Edit" class="action-btn" href="<?=url('couriers',['edit'=>$d['id']])?>"><?=ico('edit')?>
                </a>
                <form method="post" action="index.php">
                  <?php form_hidden('delete_courier','couriers',(int)$d['id']); ?>
                  <button class="action-btn action-danger" title="Hapus" type="submit" data-confirm="Hapus kurir ini? Kurir dengan riwayat pengiriman tidak dapat dihapus."><?=ico('trash')?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(!$items): ?>
          <tr>
            <td colspan="6">
              <div class="empty-state">
                <b>Belum ada kurir</b>
                <p>Tambahkan kurir melalui formulir di sebelah kanan.</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <section class="panel edit-panel">
    <h2><?=$item?'Edit Kurir':'Tambah Kurir'?>
    </h2>
    <p class="muted">Data kurir digunakan saat membuat pengiriman. Untuk memberikan akses login, buat akun role Kurir melalui menu Pengguna.</p>
    <form method="post" action="index.php" class="stack-fields">
      <?php form_hidden('save_courier','couriers',(int)($item['id']??0)); ?>
      <div class="field">
        <label>Nama Kurir <em>*</em>
        </label>
        <input name="name" required maxlength="120" value="<?=e($item['name']??'')?>" placeholder="Nama lengkap">
      </div>
      <div class="field">
        <label>Nomor Telepon <em>*</em>
        </label>
        <input name="phone" required maxlength="30" value="<?=e($item['phone']??'')?>" placeholder="08xxxxxxxxxx">
      </div>
      <div class="field">
        <label>Jenis Kendaraan</label>
        <select name="vehicle">
          <option value="">Pilih kendaraan</option>
          <?php foreach(['Motor','Mobil Box','Van','Truk','Lainnya'] as $v): ?>
          <option value="<?=e($v)?>" <?=($v===($item['vehicle']??'')?'selected':'')?>><?=e($v)?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary btn-full" type="submit"><?=ico('check')?> <?=$item?'Simpan Perubahan':'Tambah Kurir'?>
      </button>
      <?php if($item): ?>
      <a class="btn btn-light btn-full" href="<?=url('couriers')?>">Batal Edit</a>
      <?php endif; ?>
    </form>
  </section>
</div>
<?php layout_end(); }
