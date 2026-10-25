<?php

/** KirimKita — halaman profile. */

function page_profile(): void {
    $u = me();
    layout_start('profile','Profil Saya','Ubah nama, email, serta password akun Anda.'); ?>
<div class="profile-hero">
  <div class="profile-avatar"><?=e(strtoupper(mb_substr($u['name']??'A',0,1)))?>
  </div>
  <div>
    <h2><?=e($u['name'])?>
    </h2>
    <p><?=e($u['email'])?>
    </p>
    <span class="user-tag"><?=current_role() === 'courier' ? 'Kurir' : 'Administrator'?></span>
  </div>
</div>
<div class="profile-grid">
  <section class="panel">
    <div class="panel-head">
      <div>
        <h2>Informasi Akun</h2>
        <p>Perbarui data profil yang digunakan untuk login</p>
      </div>
    </div>
    <form method="post" action="index.php" class="stack-fields">
      <?php form_hidden('update_profile','profile'); ?>
      <div class="field">
        <label>Nama Lengkap <em>*</em>
        </label>
        <input name="name" required maxlength="120" value="<?=e($u['name'])?>">
      </div>
      <div class="field">
        <label>Email <em>*</em>
        </label>
        <input name="email" required type="email" maxlength="190" value="<?=e($u['email'])?>">
      </div>
      <div>
        <button type="submit" class="btn btn-primary"><?=ico('check')?> Simpan Profil</button>
      </div>
    </form>
  </section>
  <section class="panel">
    <div class="panel-head">
      <div>
        <h2>Ganti Password</h2>
        <p>Gunakan password baru minimal 8 karakter</p>
      </div>
    </div>
    <form method="post" action="index.php" class="stack-fields">
      <?php form_hidden('change_password','profile'); ?>
      <div class="field">
        <label>Password Lama</label>
        <input type="password" required name="old_password" autocomplete="current-password">
      </div>
      <div class="field">
        <label>Password Baru</label>
        <input type="password" required minlength="8" name="new_password" autocomplete="new-password">
      </div>
      <div class="field">
        <label>Konfirmasi Password Baru</label>
        <input type="password" required minlength="8" name="confirm_password" autocomplete="new-password">
      </div>
      <div>
        <button type="submit" class="btn btn-dark">Perbarui Password</button>
      </div>
    </form>
  </section>
</div>
<?php layout_end(); }
