<?php

/** KirimKita — halaman reports. */

function page_reports(): void {
    $q = trim((string)($_GET['q']??'')); $status=(string)($_GET['status']??'');
    $from=(string)($_GET['from']??''); $to=(string)($_GET['to']??''); check_date_filters($from,$to);
    $rows = filtered_shipments($q,$status,$from,$to);
    $total = count($rows); $revenue = 0; $delivered = 0; $process = 0;
    foreach($rows as $d) { if($d['status']==='delivered') { $delivered++; $revenue+=(float)$d['shipping_cost']; } if(in_array($d['status'],['picked_up','in_transit'],true)) $process++; }
    $filter = ['q'=>$q,'status'=>$status,'from'=>$from,'to'=>$to];
    layout_start('reports','Laporan Pengiriman','Pantau hasil pengiriman berdasarkan periode dan unduh datanya.'); ?>
<div class="page-actions">
  <span class="count-label">Data laporan operasional</span>
  <a class="btn btn-primary" href="<?=url('export',$filter)?>"><?=ico('download')?> Export CSV</a>
</div>
<div class="stats-grid reports-stats">
  <?php stats_card('Total Paket',(string)$total,'box','red','Sesuai filter laporan'); ?>
  <?php stats_card('Paket Terkirim',(string)$delivered,'check','green','Pengiriman selesai'); ?>
  <?php stats_card('Dalam Proses',(string)$process,'truck','blue','Sedang ditangani'); ?>
  <?php stats_card('Ongkir Terkirim',rupiah($revenue),'chart','orange','Total ongkir paket terkirim'); ?>
</div>
<section class="panel">
  <div class="panel-head">
    <div>
      <h2>Filter Laporan</h2>
      <p>Pilih rentang tanggal pembuatan pengiriman</p>
    </div>
  </div>
  <form method="get" action="index.php" class="report-filter">
    <input type="hidden" name="page" value="reports">
    <div class="field">
      <label>Dari Tanggal</label>
      <input type="date" name="from" value="<?=e($from)?>">
    </div>
    <div class="field">
      <label>Sampai Tanggal</label>
      <input type="date" name="to" value="<?=e($to)?>">
    </div>
    <div class="field">
      <label>Status</label>
      <select name="status">
        <option value="">Semua Status</option>
        <?php foreach(status_options() as $k=>$v): ?>
        <option value="<?=e($k)?>" <?=($status===$k?'selected':'')?>><?=e($v)?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label>Pencarian</label>
      <input name="q" placeholder="Resi / pelanggan / kota" value="<?=e($q)?>">
    </div>
    <button type="submit" class="btn btn-dark">Terapkan Filter</button>
    <a href="<?=url('reports')?>" class="btn btn-light">Reset</a>
  </form>
</section>
<section class="panel">
  <div class="panel-head">
    <div>
      <h2>Rincian Laporan</h2>
      <p><?=count($rows)?> baris sesuai filter yang dipilih</p>
    </div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>No. Resi</th>
          <th>Pelanggan</th>
          <th>Tujuan</th>
          <th>Ongkir</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $d): ?>
        <tr>
          <td><?=e(tanggal($d['created_at']))?>
          </td>
          <td>
            <a class="strong-link mono" href="<?=url('shipment',['id'=>$d['id']])?>"><?=e($d['tracking_number'])?>
            </a>
          </td>
          <td><?=e($d['customer_name'])?>
          </td>
          <td><?=e($d['destination_city'])?>
          </td>
          <td><?=rupiah($d['shipping_cost'])?>
          </td>
          <td><?=badge_status($d['status'])?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?>
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <b>Tidak ada hasil</b>
              <p>Coba ubah rentang tanggal atau filter status.</p>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php layout_end(); }
