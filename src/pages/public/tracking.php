<?php

/** KirimKita — halaman tracking. */

function page_tracking(): void {
    $resi = strtoupper(trim((string)($_GET['resi']??'')));
    $s = null; $events=[];
    if ($resi!=='') {
        if (strlen($resi)>40) $resi=substr($resi,0,40);
        $s = one('SELECT tracking_number,destination_city,service_type,status,created_at FROM shipments WHERE tracking_number=?',[$resi]);
        if($s) $events = many('SELECT status,description,created_at FROM tracking_events WHERE shipment_id=(SELECT id FROM shipments WHERE tracking_number=?) ORDER BY created_at DESC,id DESC',[$resi]);
    }
    top_html('Lacak Paket'); ?>
<div class="tracking-layout">
  <header class="tracking-header">
    <a class="track-brand" href="<?=url(signed_in()?'dashboard':'login')?>">
      <span class="brand-icon">KK</span>
      <span>
        <b>KirimKita</b>
        <small>LOGISTICS PLATFORM</small>
      </span>
    </a>
    <a class="btn btn-light" href="<?=url(signed_in()?'dashboard':'login')?>"><?=signed_in()?'Kembali ke Dashboard':'Login Petugas'?> <?=ico('arrow')?>
    </a>
  </header>
  <section class="tracking-intro">
    <div class="tracking-overline">KIRIMKITA EXPRESS CARGO</div>
    <h1>Lacak Paket Kamu<br>
      <span>Dengan Mudah</span>
    </h1>
    <p>Masukkan nomor resi untuk mengetahui perkembangan pengiriman paket kamu secara real-time berdasarkan pembaruan petugas.</p>
    <form action="index.php" method="get" class="tracking-search">
      <input type="hidden" name="page" value="tracking">
      <div><?=ico('search')?>
        <input name="resi" maxlength="40" required value="<?=e($resi)?>" placeholder="Masukkan nomor resi (contoh: KK26102500000001)">
      </div>
      <button type="submit" class="btn btn-primary">Lacak Paket <?=ico('arrow')?>
      </button>
    </form>
  </section>
  <?php if($resi!==''): ?>
  <div class="tracking-result">
    <?php if(!$s): ?>
    <section class="panel notfound">
      <div class="empty-circle"><?=ico('box')?>
      </div>
      <h2>Nomor resi tidak ditemukan</h2>
      <p>Pastikan nomor resi yang dimasukkan benar, lalu coba lagi.</p>
    </section>
    <?php else: ?>
    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">HASIL PELACAKAN</p>
          <h2 class="mono"><?=e($s['tracking_number'])?>
          </h2>
        </div><?=badge_status($s['status'])?>
      </div>
      <div class="track-info">
        <div>
          <small>Tujuan</small>
          <b><?=e($s['destination_city'])?>
          </b>
        </div>
        <div>
          <small>Layanan</small>
          <b><?=e($s['service_type'])?>
          </b>
        </div>
        <div>
          <small>Dibuat</small>
          <b><?=e(tanggal($s['created_at']))?>
          </b>
        </div>
      </div>
    </section>
    <section class="panel">
      <div class="panel-head">
        <div>
          <h2>Riwayat Perjalanan</h2>
          <p>Pembaruan status terakhir muncul paling atas</p>
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
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="tracking-features">
    <div>
      <span><?=ico('box')?>
      </span>
      <b>Pengiriman Praktis</b>
      <p>Paket dikelola secara terorganisir.</p>
    </div>
    <div>
      <span><?=ico('truck')?>
      </span>
      <b>Status Terbarui</b>
      <p>Perjalanan tercatat oleh petugas.</p>
    </div>
    <div>
      <span><?=ico('check')?>
      </span>
      <b>Resi Mudah Dilacak</b>
      <p>Cukup gunakan nomor resi.</p>
    </div>
  </div>
  <footer class="tracking-footer">© <?=date('Y')?> KirimKita Logistics Platform • Tugas Besar Manajemen Proyek</footer>
</div>
<?php end_html(); }
