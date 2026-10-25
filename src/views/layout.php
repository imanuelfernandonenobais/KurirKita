<?php

/** Komponen layout, ikon, dan formulir yang dipakai semua halaman. */

function ico(string $name, string $class = ''): string { return '<svg class="icon '.e($class).'" width="18" height="18" aria-hidden="true"><use href="#i-'.e($name).'"></use></svg>'; }
function icons(): void { ?>
<svg xmlns="http://www.w3.org/2000/svg" style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
  <symbol id="i-dashboard" viewBox="0 0 24 24">
    <rect x="3" y="3" width="7" height="9" rx="1"/>
    <rect x="14" y="3" width="7" height="5" rx="1"/>
    <rect x="14" y="12" width="7" height="9" rx="1"/>
    <rect x="3" y="16" width="7" height="5" rx="1"/>
  </symbol>
  <symbol id="i-box" viewBox="0 0 24 24">
    <path d="M3 7 12 2l9 5v10l-9 5-9-5zM3 7l9 5 9-5M12 12v10"/>
  </symbol>
  <symbol id="i-users" viewBox="0 0 24 24">
    <circle cx="9" cy="7" r="4"/>
    <path d="M2 21v-3a7 7 0 0 1 14 0v3M17 4a4 4 0 0 1 0 8M17 15a6 6 0 0 1 5 6"/>
  </symbol>
  <symbol id="i-truck" viewBox="0 0 24 24">
    <path d="M1 5h14v12H1zM15 9h4l4 4v4h-8z"/>
    <circle cx="5" cy="18" r="2"/>
    <circle cx="19" cy="18" r="2"/>
  </symbol>
  <symbol id="i-chart" viewBox="0 0 24 24">
    <path d="M3 3v18h18M7 16v-4M12 16V7M17 16v-7"/>
  </symbol>
  <symbol id="i-user" viewBox="0 0 24 24">
    <circle cx="12" cy="8" r="4"/>
    <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
  </symbol>
  <symbol id="i-exit" viewBox="0 0 24 24">
    <path d="M9 3H4v18h5M14 7l5 5-5 5M8 12h11"/>
  </symbol>
  <symbol id="i-plus" viewBox="0 0 24 24">
    <path d="M12 5v14M5 12h14"/>
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24">
    <circle cx="11" cy="11" r="7"/>
    <path d="m16 16 5 5"/>
  </symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24">
    <path d="M5 12h14m-6-6 6 6-6 6"/>
  </symbol>
  <symbol id="i-back" viewBox="0 0 24 24">
    <path d="M19 12H5m6 6-6-6 6-6"/>
  </symbol>
  <symbol id="i-edit" viewBox="0 0 24 24">
    <path d="m4 16-1 5 5-1L20 8l-4-4L4 16zM14 6l4 4"/>
  </symbol>
  <symbol id="i-trash" viewBox="0 0 24 24">
    <path d="M4 7h16M10 3h4M6 7l1 14h10l1-14M10 11v6M14 11v6"/>
  </symbol>
  <symbol id="i-eye" viewBox="0 0 24 24">
    <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"/>
    <circle cx="12" cy="12" r="3"/>
  </symbol>
  <symbol id="i-pin" viewBox="0 0 24 24">
    <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/>
    <circle cx="12" cy="10" r="2"/>
  </symbol>
  <symbol id="i-download" viewBox="0 0 24 24">
    <path d="M12 3v13m-5-5 5 5 5-5M4 17v4h16v-4"/>
  </symbol>
  <symbol id="i-check" viewBox="0 0 24 24">
    <path d="m4 12 5 5L20 6"/>
  </symbol>
  <symbol id="i-menu" viewBox="0 0 24 24">
    <path d="M3 6h18M3 12h18M3 18h18"/>
  </symbol>
  <symbol id="i-history" viewBox="0 0 24 24">
    <path d="M3 12a9 9 0 1 0 3-7M3 3v6h6M12 7v5l4 3"/>
  </symbol>
  <symbol id="i-qr" viewBox="0 0 24 24">
    <path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM15 15h2v2h-2zM20 14v3M14 20h3M20 20h1"/>
  </symbol>
  <symbol id="i-warehouse" viewBox="0 0 24 24">
    <path d="m3 10 9-7 9 7v11H3zM3 10h18M8 21v-8h8v8"/>
  </symbol>
  <symbol id="i-copy" viewBox="0 0 24 24">
    <rect x="8" y="8" width="12" height="12" rx="2"/>
    <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
  </symbol>
</svg>
<?php }
function top_html(string $title): void { ?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#df252b">
    <title><?=e($title)?> - KirimKita</title>
    <link rel="stylesheet" href="assets/style.css">
  </head>
  <body>
    <?php icons(); }
function end_html(): void { ?>
    <script src="assets/app.js">
    </script>
  </body>
</html>
<?php }
/** Sidebar dan topbar dibuat sesuai role, tanpa menggandakan layout. */
function layout_start(string $selected, string $title, string $subtitle): void
{
    $user = me() ?? [];
    $role = current_role();
    $isCourier = $role === 'courier';
    $nav = $isCourier
        ? [
            'dashboard'       => ['dashboard', 'Dashboard'],
            'courier-tasks'   => ['truck', 'Tugas Pengiriman'],
            'courier-scan'    => ['qr', 'Scan / Cari Paket'],
            'courier-history' => ['history', 'Riwayat'],
            'profile'         => ['user', 'Profil'],
        ]
        : [
            'dashboard' => ['dashboard', 'Dashboard'],
            'shipments' => ['truck', 'Pengiriman'],
            'tracking'  => ['pin', 'Tracking Resi'],
            'couriers'  => ['truck', 'Armada / Kurir'],
            'depots'    => ['warehouse', 'Depo'],
            'users'    => ['users', 'Pengguna'],
            'customers'=> ['user', 'Pelanggan'],
            'reports'  => ['chart', 'Laporan'],
            'profile'  => ['user', 'Profil'],
        ];

    top_html($title);
    ?>
    <div class="app-shell <?= $isCourier ? 'courier-shell' : 'admin-shell' ?>">
        <aside class="sidebar" id="sidebar">
            <a class="sidebar-brand" href="<?= url('dashboard') ?>">
                <span class="brand-icon"><?= $isCourier ? ico('truck') : 'KK' ?></span>
                <span><b>KirimKita</b><small>Logistics Platform</small></span>
            </a>
            <div class="nav-label"><?= $isCourier ? 'MENU KURIR' : 'MENU UTAMA' ?></div>
            <nav class="nav-links" aria-label="Menu <?= $isCourier ? 'kurir' : 'admin' ?>">
                <?php foreach ($nav as $key => [$icon, $label]): ?>
                    <a class="nav-link <?= $selected === $key ? 'active' : '' ?>"
                       href="<?= url($key) ?>"
                       <?= $selected === $key ? 'aria-current="page"' : '' ?>>
                        <?= ico($icon) ?><span><?= e($label) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-bottom">
                <div class="sidebar-support">
                    <span class="mini-avatar"><?= e(mb_strtoupper(mb_substr($user['name'] ?? 'K', 0, 1))) ?></span>
                    <div><b><?= e($user['name'] ?? 'Petugas') ?></b>
                        <small><?= $isCourier ? 'Akun Kurir' : 'Administrator' ?></small></div>
                </div>
                <form action="index.php" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="nav-link logout"><?= ico('exit') ?> Keluar</button>
                </form>
            </div>
        </aside>
        <div class="sidebar-overlay" data-sidebar-close></div>
        <div class="main-wrap">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="icon-button mobile-menu" data-sidebar-toggle
                            aria-label="Buka navigasi"><?= ico('menu') ?></button>
                    <div class="breadcrumb">KirimKita <span>/</span> <b><?= e($title) ?></b></div>
                </div>
                <div class="topbar-right">
                    <a class="track-top" href="<?= url('tracking') ?>" target="_blank" rel="noopener">Lacak Paket</a>
                    <span class="top-avatar"><?= e(mb_strtoupper(mb_substr($user['name'] ?? 'K', 0, 1))) ?></span>
                    <div class="top-account"><b><?= e($user['name'] ?? 'Petugas') ?></b>
                        <small><?= $isCourier ? 'Kurir' : 'Administrator' ?></small></div>
                </div>
            </header>
            <main class="main-content">
                <div class="page-heading"><div><h1><?= e($title) ?></h1><p><?= e($subtitle) ?></p></div></div>
                <?php show_flash(); ?>
    <?php
}

function layout_end(): void
{
    ?>
            </main>
            <footer class="app-footer">© <?= date('Y') ?> KirimKita Logistics Platform <span>•</span> Proyek Tugas Besar Manajemen Proyek</footer>
        </div>
    </div>
    <?php
    end_html();
}

function form_hidden(string $action, string $return, int $id=0): void { echo csrf_field().'<input type="hidden" name="action" value="'.e($action).'"><input type="hidden" name="return_page" value="'.e($return).'">'.($id?'<input type="hidden" name="id" value="'.$id.'">':''); }
function stats_card(string $title, string $value, string $icon, string $tone, string $note): void { ?>
<div class="stat-card">
  <div class="stat-icon stat-<?=e($tone)?>"><?=ico($icon)?>
  </div>
  <div class="stat-meta">
    <span><?=e($title)?>
    </span>
    <strong><?=e($value)?>
    </strong>
    <small><?=e($note)?>
    </small>
  </div>
</div>
<?php }
