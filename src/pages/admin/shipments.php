<?php

/** KirimKita — halaman shipments. */

function shipment_table(array $rows, bool $actions = true): void { ?>
<div class="table-scroll">
  <table class="data-table">
    <thead>
      <tr>
        <th>No. Resi / Tanggal</th>
        <th>Pengirim / Penerima</th>
        <th>Tujuan</th>
        <th>Layanan</th>
        <th>Kurir</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $d): ?>
      <tr>
        <td>
          <a class="strong-link mono" href="<?=url('shipment',['id'=>$d['id']])?>"><?=e($d['tracking_number'])?>
          </a>
          <small class="table-sub"><?=e(tanggal($d['created_at']))?>
          </small>
        </td>
        <td>
          <b><?=e($d['customer_name'])?>
          </b>
          <small class="table-sub">Ke: <?=e($d['recipient_name'])?>
          </small>
        </td>
        <td><?=e($d['destination_city'])?>
        </td>
        <td>
          <span class="service-pill"><?=e($d['service_type'])?>
          </span>
        </td>
        <td><?=e($d['courier_name'] ?: 'Belum ditugaskan')?>
        </td>
        <td><?=badge_status($d['status'])?>
        </td>
        <td>
          <div class="action-buttons">
            <a title="Detail" class="action-btn" href="<?=url('shipment',['id'=>$d['id']])?>"><?=ico('eye')?>
            </a>
            <?php if($actions): ?>
            <a title="Edit" class="action-btn" href="<?=url('shipment-form',['id'=>$d['id']])?>"><?=ico('edit')?>
            </a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <div><?=ico('box')?>
            </div>
            <b>Belum ada pengiriman</b>
            <p>Tambahkan data atau ubah filter pencarian.</p>
          </div>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php }

function page_shipments(): void {
    $q = trim((string)($_GET['q'] ?? '')); $status = (string)($_GET['status'] ?? '');
    $rows = filtered_shipments($q,$status);
    layout_start('shipments','Data Pengiriman','Kelola seluruh paket, resi, kurir, dan status pengiriman.'); ?>
<div class="page-actions">
  <span class="count-label"><?=count($rows)?> pengiriman ditemukan</span>
  <a class="btn btn-primary" href="<?=url('shipment-form')?>"><?=ico('plus')?> Tambah Pengiriman</a>
</div>
<section class="panel">
  <form method="get" action="index.php" class="filter-bar">
    <input type="hidden" name="page" value="shipments">
    <div class="search-field"><?=ico('search')?>
      <input name="q" value="<?=e($q)?>" placeholder="Cari resi, pengirim, penerima, atau kota...">
    </div>
    <select name="status">
      <option value="">Semua Status</option>
      <?php foreach(status_options() as $k=>$v): ?>
      <option value="<?=e($k)?>" <?=($status===$k?'selected':'')?>><?=e($v)?>
      </option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-dark" type="submit">Cari</button>
    <?php if($q!=='' || $status!==''): ?>
    <a href="<?=url('shipments')?>" class="btn btn-light">Reset</a>
    <?php endif; ?>
  </form>
  <?php shipment_table($rows); ?>
</section>
<?php layout_end(); }
