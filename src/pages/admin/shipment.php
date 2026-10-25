<?php

/** KirimKita — halaman shipment. */

function page_shipment(): void {
    $id = max(0,(int)($_GET['id']??0));
    $s = one('SELECT s.*,c.name AS customer_name,c.phone AS customer_phone,k.name AS courier_name,k.phone AS courier_phone FROM shipments s JOIN customers c ON c.id=s.customer_id LEFT JOIN couriers k ON k.id=s.courier_id WHERE s.id=?', [$id]);
    if (!$s) { http_response_code(404); layout_start('shipments','Pengiriman Tidak Ditemukan','Nomor pengiriman tidak ada di database.'); echo '<a class="btn btn-light" href="'.url('shipments').'">Kembali</a>'; layout_end(); return; }
    $events = many('SELECT * FROM tracking_events WHERE shipment_id=? ORDER BY created_at DESC,id DESC',[$id]);
    layout_start('shipments','Detail Pengiriman','Informasi paket, pembaruan status, dan riwayat pelacakan.'); ?>
<div class="page-actions">
  <a class="btn btn-light" href="<?=url('shipments')?>"><?=ico('back')?> Kembali</a>
  <div class="button-cluster">
    <a href="<?=url('receipt',['id'=>$id])?>" class="btn btn-light"><?=ico('download')?> Cetak Resi</a>
    <a href="<?=url('shipment-form',['id'=>$id])?>" class="btn btn-light"><?=ico('edit')?> Edit</a>
    <button class="btn btn-outline-danger" type="submit" form="delete-shipment" data-confirm="Yakin ingin menghapus pengiriman ini beserta riwayat pelacakannya?"> <?=ico('trash')?> Hapus</button>
  </div>
</div>
<form id="delete-shipment" method="post" action="index.php" class="visually-hidden">
  <?php form_hidden('delete_shipment','shipments',$id); ?>
</form>
<div class="detail-hero">
  <div>
    <div class="eyebrow">NOMOR RESI</div>
    <div class="resi-line">
      <h2 class="mono"><?=e($s['tracking_number'])?>
      </h2>
      <button type="button" class="icon-button copy-resi" data-copy="<?=e($s['tracking_number'])?>" title="Salin resi"><?=ico('copy')?>
      </button>
    </div>
    <p>Dibuat <?=e(tanggal($s['created_at'],true))?> • <?=e($s['service_type'])?> • <?=e($s['weight_kg'])?> kg</p>
  </div>
  <div class="hero-status"><?=badge_status($s['status'])?>
    <a href="<?=url('tracking',['resi'=>$s['tracking_number']])?>" target="_blank" rel="noopener">Lihat halaman pelacakan ↗</a>
  </div>
</div>
<div class="detail-grid">
  <div class="detail-main">
    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Informasi Paket</h2>
          <p>Data lengkap pengirim dan penerima</p>
        </div>
      </div>
      <div class="info-grid">
        <div>
          <span>Nama Pengirim</span>
          <b><?=e($s['customer_name'])?>
          </b>
        </div>
        <div>
          <span>Telepon Pengirim</span>
          <b><?=e($s['customer_phone'])?>
          </b>
        </div>
        <div>
          <span>Nama Penerima</span>
          <b><?=e($s['recipient_name'])?>
          </b>
        </div>
        <div>
          <span>Telepon Penerima</span>
          <b><?=e($s['recipient_phone'])?>
          </b>
        </div>
        <div>
          <span>Alamat Asal</span>
          <b><?=e($s['origin_address'])?>
          </b>
        </div>
        <div>
          <span>Alamat Tujuan</span>
          <b><?=e($s['destination_address'])?>
          </b>
        </div>
        <div>
          <span>Kota Tujuan</span>
          <b><?=e($s['destination_city'])?>
          </b>
        </div>
        <div>
          <span>Kurir</span>
          <b><?=e($s['courier_name'] ?: 'Belum ditugaskan')?>
          </b>
        </div>
        <div>
          <span>Jenis Layanan</span>
          <b><?=e($s['service_type'])?>
          </b>
        </div>
        <div>
          <span>Biaya Pengiriman</span>
          <b class="red-text"><?=rupiah($s['shipping_cost'])?>
          </b>
        </div>
        <?php if($s['notes']): ?>
        <div class="info-span">
          <span>Catatan</span>
          <b><?=e($s['notes'])?>
          </b>
        </div>
        <?php endif; ?>
      </div>
    </section>
    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Riwayat Pelacakan</h2>
          <p>Semua pembaruan tercatat secara berurutan</p>
        </div>
      </div>
      <div class="timeline">
        <?php foreach($events as $idx=>$ev): ?>
        <div class="timeline-item <?=($idx===0?'latest':'')?>">
          <div class="timeline-dot"><?=($idx===0?ico('check'):'')?>
          </div>
          <div>
            <b><?=e(status_name($ev['status']))?>
            </b>
            <small><?=e(tanggal($ev['created_at'],true))?>
            </small>
            <p><?=e($ev['description'])?>
            </p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
  <div class="detail-side">
    <section class="panel">
      <h2>Perbarui Status</h2>
      <p class="muted">Setiap perubahan akan tampil di halaman pelacakan publik.</p>
      <form method="post" action="index.php" class="stack-fields">
        <?php form_hidden('update_status','shipment',$id); ?>
        <div class="field">
          <label>Status Paket</label>
          <select name="status" required>
            <?php foreach(status_options() as $k=>$label): ?>
            <option value="<?=e($k)?>" <?=($k===$s['status']?'selected':'')?>><?=e($label)?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Keterangan Status</label>
          <textarea name="description" rows="4" maxlength="500" placeholder="Contoh: Paket telah tiba di gudang sortir.">
          </textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Simpan Status <?=ico('arrow')?>
        </button>
      </form>
    </section>
    <section class="panel mini-note">
      <h3><?=ico('pin')?> Pelacakan Publik</h3>
      <p>Pelanggan dapat mengetik nomor resi ini tanpa login.</p>
      <button type="button" class="btn btn-light btn-full" data-copy="<?=e($s['tracking_number'])?>"><?=ico('copy')?> Salin Nomor Resi</button>
    </section>
  </div>
</div>
<?php layout_end(); }
