<?php

/** KirimKita — halaman shipment-form. */

function page_shipment_form(): void {
    $id = max(0,(int)($_GET['id'] ?? 0));
    $d = $id ? one('SELECT * FROM shipments WHERE id=?',[$id]) : null;
    if ($id && !$d) { http_response_code(404); layout_start('shipments','Pengiriman Tidak Ditemukan','Periksa kembali data pengiriman.'); echo '<a href="'.url('shipments').'">Kembali</a>'; layout_end(); return; }
    $customers = many('SELECT id,name,phone FROM customers ORDER BY name');
    $couriers = many('SELECT id,name,vehicle FROM couriers ORDER BY name');
    layout_start('shipments',$id?'Edit Pengiriman':'Tambah Pengiriman', $id?'Perbarui rincian paket tanpa mengubah nomor resi.':'Isi data paket baru untuk menghasilkan nomor resi otomatis.'); ?>
<div class="page-actions">
  <a class="btn btn-light" href="<?=url('shipments')?>"><?=ico('back')?> Kembali</a>
</div>
<form method="post" action="index.php" class="form-grid">
  <?php form_hidden('save_shipment','shipment-form',$id); ?>
  <section class="panel form-card">
    <div class="panel-head">
      <div>
        <h2>Informasi Pengiriman</h2>
        <p>Data pengirim, penerima, dan perjalanan paket</p>
      </div>
    </div>
    <div class="fields two-col">
      <div class="field">
        <label>Pelanggan / Pengirim <em>*</em>
        </label>
        <select name="customer_id" required>
          <option value="">Pilih pelanggan</option>
          <?php foreach($customers as $c): ?>
          <option value="<?=$c['id']?>" <?=((int)($d['customer_id']??0)===(int)$c['id']?'selected':'')?>><?=e($c['name'].' — '.$c['phone'])?>
          </option>
          <?php endforeach; ?>
        </select>
        <small>Belum ada pelanggan? <a href="<?=url('customers')?>">Tambah pelanggan</a>
        </small>
      </div>
      <div class="field">
        <label>Kurir</label>
        <select name="courier_id">
          <option value="0">Belum ditugaskan</option>
          <?php foreach($couriers as $k): ?>
          <option value="<?=$k['id']?>" <?=((int)($d['courier_id']??0)===(int)$k['id']?'selected':'')?>><?=e($k['name'].' — '.$k['vehicle'])?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Nama Penerima <em>*</em>
        </label>
        <input name="recipient_name" maxlength="120" required value="<?=e($d['recipient_name']??'')?>" placeholder="Contoh: Rina Kurnia">
      </div>
      <div class="field">
        <label>No. HP Penerima <em>*</em>
        </label>
        <input name="recipient_phone" maxlength="30" required value="<?=e($d['recipient_phone']??'')?>" placeholder="08xxxxxxxxxx">
      </div>
      <div class="field">
        <label>Alamat Asal <em>*</em>
        </label>
        <textarea name="origin_address" rows="3" maxlength="500" required placeholder="Alamat lengkap pengirim"><?=e($d['origin_address']??'')?>
        </textarea>
      </div>
      <div class="field">
        <label>Alamat Tujuan <em>*</em>
        </label>
        <textarea name="destination_address" rows="3" maxlength="500" required placeholder="Alamat lengkap penerima"><?=e($d['destination_address']??'')?>
        </textarea>
      </div>
      <div class="field">
        <label>Kota Tujuan <em>*</em>
        </label>
        <input name="destination_city" maxlength="100" required value="<?=e($d['destination_city']??'')?>" placeholder="Bandung">
      </div>
      <div class="field">
        <label>Jenis Layanan <em>*</em>
        </label>
        <select name="service_type">
          <option value="REG" <?=(($d['service_type']??'REG')==='REG'?'selected':'')?>>REG — Reguler</option>
          <option value="EXPRESS" <?=(($d['service_type']??'')==='EXPRESS'?'selected':'')?>>EXPRESS — Kilat</option>
        </select>
      </div>
      <div class="field">
        <label>Berat (kg) <em>*</em>
        </label>
        <input type="number" name="weight_kg" min="0.01" max="100000" step="0.01" value="<?=e($d['weight_kg']??'1')?>" required>
      </div>
      <div class="field">
        <label>Ongkos Kirim (Rp) <em>*</em>
        </label>
        <input type="number" name="shipping_cost" min="0" max="1000000000" step="1000" value="<?=money_input($d['shipping_cost']??'15000')?>" required>
      </div>
      <div class="field full-col">
        <label>Catatan (Opsional)</label>
        <textarea name="notes" rows="3" maxlength="500" placeholder="Catatan penjemputan / instruksi tambahan"><?=e($d['notes']??'')?>
        </textarea>
      </div>
    </div>
    <div class="form-footer">
      <a class="btn btn-light" href="<?=url('shipments')?>">Batal</a>
      <button class="btn btn-primary" type="submit"><?=ico('check')?> <?=$id?'Simpan Perubahan':'Buat Pengiriman'?>
      </button>
    </div>
  </section>
  <aside class="form-help">
    <div class="panel">
      <h3><?=ico('box')?> Info Pengiriman</h3>
      <p>Nomor resi dibuat otomatis ketika Anda menyimpan pengiriman baru.</p>
      <hr>
      <h4>Alur sederhana</h4>
      <ol>
        <li>Pilih pelanggan dan kurir.</li>
        <li>Masukkan data paket.</li>
        <li>Simpan dan dapatkan nomor resi.</li>
        <li>Update status di halaman detail.</li>
        <li>Pelanggan dapat melacak paket melalui resi.</li>
      </ol>
    </div>
  </aside>
</form>
<?php layout_end(); }
