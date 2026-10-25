<?php

/** KirimKita — halaman dashboard. */

function page_dashboard(): void {
    $totals = one("SELECT COUNT(*) AS total, COALESCE(SUM(status='pending'),0) AS pending, COALESCE(SUM(status='in_transit' OR status='picked_up'),0) AS process, COALESCE(SUM(status='delivered'),0) AS delivered, COALESCE(SUM(status='cancelled'),0) AS cancelled, COALESCE(SUM(CASE WHEN status='delivered' THEN shipping_cost ELSE 0 END),0) AS revenue FROM shipments") ?? [];
    $recent = filtered_shipments('', '', '', '', 7);
    $all = max(1, (int)($totals['total'] ?? 0));
    layout_start('dashboard', 'Dashboard', 'Ringkasan aktivitas pengiriman dan performa operasional.'); ?>
<div class="page-actions">
  <span class="date-pill"><?=e(date('l, d F Y'))?>
  </span>
  <a href="<?=url('shipment-form')?>" class="btn btn-primary"><?=ico('plus')?> Tambah Pengiriman</a>
</div>
<div class="stats-grid">
  <?php stats_card('Total Pengiriman', number_format((int)$totals['total'],0,',','.'),'box','red','Seluruh data pengiriman'); ?>
  <?php stats_card('Dalam Proses', number_format((int)$totals['process'],0,',','.'),'truck','blue','Dijemput / perjalanan'); ?>
  <?php stats_card('Terkirim', number_format((int)$totals['delivered'],0,',','.'),'check','green','Paket berhasil diterima'); ?>
  <?php stats_card('Menunggu Pickup',number_format((int)$totals['pending'],0,',','.'),'box','orange','Belum dijemput kurir'); ?>
</div>
<div class="dashboard-grids">
  <section class="panel">
    <div class="panel-head">
      <div>
        <h2>Ringkasan Pengiriman</h2>
        <p>Komposisi status seluruh paket</p>
      </div>
      <a class="text-link" href="<?=url('reports')?>">Lihat Laporan <?=ico('arrow')?>
      </a>
    </div>
    <div class="summary-bars">
      <?php foreach (['delivered'=>['Terkirim','green'],'in_process'=>['Dalam Proses','blue'],'pending'=>['Menunggu Pickup','orange'],'cancelled'=>['Dibatalkan','gray']] as $key=>$props): $value=($key==='in_process' ? (int)$totals['process'] : (int)($totals[$key] ?? 0)); $pct=round(100*$value/$all); ?>
      <div class="progress-row">
        <div class="progress-label">
          <span><?=e($props[0])?>
          </span>
          <b><?=$value?> <small>(<?=$pct?>%)</small>
          </b>
        </div>
        <div class="progress-track">
          <div class="progress-fill bar-<?=e($props[1])?>" style="width:<?=$pct?>%">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="panel quick-panel">
    <div class="panel-head">
      <div>
        <h2>Akses Cepat</h2>
        <p>Menu yang sering digunakan</p>
      </div>
    </div>
    <div class="quick-links">
      <a href="<?=url('shipment-form')?>">
        <span class="quick-ico quick-red"><?=ico('plus')?>
        </span>
        <span>
          <b>Buat Pengiriman</b>
          <small>Catat paket baru</small>
        </span><?=ico('arrow')?>
      </a>
      <a href="<?=url('shipments')?>">
        <span class="quick-ico quick-blue"><?=ico('box')?>
        </span>
        <span>
          <b>Kelola Pengiriman</b>
          <small>Ubah data dan status paket</small>
        </span><?=ico('arrow')?>
      </a>
      <a href="<?=url('customers')?>">
        <span class="quick-ico quick-orange"><?=ico('users')?>
        </span>
        <span>
          <b>Data Pelanggan</b>
          <small>Kelola pengirim</small>
        </span><?=ico('arrow')?>
      </a>
      <a href="<?=url('tracking')?>" target="_blank">
        <span class="quick-ico quick-green"><?=ico('pin')?>
        </span>
        <span>
          <b>Lacak Paket</b>
          <small>Cek perjalanan paket</small>
        </span><?=ico('arrow')?>
      </a>
    </div>
  </section>
</div>
<section class="panel">
  <div class="panel-head">
    <div>
      <h2>Pengiriman Terbaru</h2>
      <p>Daftar paket yang baru masuk ke sistem</p>
    </div>
    <a class="btn btn-light btn-sm" href="<?=url('shipments')?>">Lihat Semua <?=ico('arrow')?>
    </a>
  </div>
  <?php shipment_table($recent,false); ?>
</section>
<?php layout_end(); }
