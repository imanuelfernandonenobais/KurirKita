<?php

/** KirimKita — halaman login. */

function login_art(): void { ?>
<div class="login-art">
  <div class="art-cloud cloud-a">
  </div>
  <div class="art-cloud cloud-b">
  </div>
  <div class="art-cloud cloud-c">
  </div>
  <div class="art-ground">
  </div>
  <div class="art-truck">
    <div class="truck-van">
      <div class="truck-logo">
        <b>KK</b>
        <span>
          <strong>KirimKita</strong>
          <small>EXPRESS CARGO</small>
        </span>
      </div>
      <div class="truck-lines">#KirimAjaPastiSampai<br>Express • Cepat • Aman</div>
    </div>
    <div class="truck-cabin">
      <div class="truck-window">
      </div>
    </div>
    <div class="wheel wheel-one">
    </div>
    <div class="wheel wheel-two">
    </div>
    <div class="wheel wheel-three">
    </div>
  </div>
  <div class="art-boxes boxes-left">
    <i>
    </i>
    <i>
    </i>
    <i>
    </i>
  </div>
  <div class="art-boxes boxes-right">
    <i>
    </i>
    <i>
    </i>
    <i>
    </i>
  </div>
  <div class="art-warehouse">
    <div class="warehouse-roof">
    </div>
    <div class="warehouse-pin">
    </div>
    <div class="warehouse-body">
      <div class="warehouse-brand">
        <b>KK</b> KIRIMKITA HUB</div>
      <div class="warehouse-door">
      </div>
    </div>
  </div>
</div>
<?php }

function page_login(): void { top_html('Masuk ke Sistem'); ?>
<div class="login-scene">
  <?php login_art(); ?>
  <div class="login-card">
    <div class="brand-icon login-brand">KK</div>
    <div class="login-logotype">
      <strong>KirimKita</strong>
      <small>LOGISTICS PLATFORM</small>
    </div>
    <h1>Masuk ke Sistem</h1>
    <p class="login-intro">Silakan masuk menggunakan akun operasional Anda.</p>
    <?php show_flash(); ?>
    <form method="post" action="index.php" class="login-form">
      <?php form_hidden('login','login'); ?>
      <label for="login-email">Email</label>
      <div class="input-symbol">
        <span>✉</span>
        <input id="login-email" name="email" type="email" placeholder="admin@kirimkita.id" autocomplete="username" required>
      </div>
      <div class="field-head">
        <label for="login-password">Password</label>
        <span class="hint-password">Akun demo tersedia setelah instalasi</span>
      </div>
      <div class="input-symbol">
        <span>♙</span>
        <input id="login-password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
        <button type="button" class="password-toggle" data-toggle-password aria-label="Tampilkan password">◉</button>
      </div>
      <label class="remember-line">
        <input type="checkbox" checked disabled> Sesi aman pada perangkat ini</label>
      <button class="btn btn-primary btn-full" type="submit">Masuk <?=ico('arrow')?>
      </button>
    </form>
    <a class="login-track-link" href="<?=url('tracking')?>">Lacak paket tanpa login →</a>
    <div class="login-foot">PT KirimKita Logistics Nusantara <small>© <?=date('Y')?> KirimKita Logistics Platform. All Rights Reserved.</small>
    </div>
  </div>
  <div class="login-caption">Kelola pengiriman dengan lebih mudah, cepat, dan rapi.</div>
</div>
<?php end_html(); }
