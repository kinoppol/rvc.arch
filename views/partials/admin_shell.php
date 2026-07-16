<?php
/** @var string $content @var string $adminView @var string $title */
$user = Auth::user() ?? ['name' => '', 'role' => ''];
$nav = function (string $key) use ($adminView): string {
    $active = $adminView === $key;
    return 'display:flex;align-items:center;gap:11px;width:100%;text-align:left;border:none;border-radius:9px;'
        . 'padding:10px 12px;font-size:13.5px;font-weight:' . ($active ? '600' : '500') . ';cursor:pointer;'
        . 'background:' . ($active ? 'var(--primary-soft)' : 'transparent') . ';'
        . 'color:' . ($active ? 'var(--primary-text)' : 'var(--muted)') . ';white-space:nowrap;text-decoration:none';
};
$items = [
    ['dashboard',  'admin',            '▦', 'แดชบอร์ด'],
    ['research',   'admin/research',   '≣', 'จัดการงานวิจัย'],
    ['submit',     'admin/submit',     '↑', 'นำเข้างานวิจัย'],
];
$settings = [
    ['categories', 'admin/categories', '◈', 'จัดการประเภท'],
    ['users',      'admin/users',      '◍', 'จัดการผู้ใช้'],
];
?>
<div style="display:flex;min-height:100vh">
  <!-- sidebar -->
  <aside data-sidebar data-collapsed="0" style="width:240px;flex:none;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;transition:width .18s;overflow:hidden">
    <div style="height:64px;display:flex;align-items:center;gap:11px;padding:0 18px;border-bottom:1px solid var(--border)">
      <div style="width:36px;height:36px;border-radius:9px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;flex:none">RV</div>
      <div data-collapse-hide style="line-height:1.15;white-space:nowrap"><div style="font-weight:700;font-size:14px">ผู้ดูแลระบบ</div><div style="font-size:11px;color:var(--muted)">Research Repository</div></div>
    </div>
    <nav style="padding:14px 12px;display:flex;flex-direction:column;gap:3px;flex:1;overflow-y:auto">
      <div data-collapse-hide style="font-size:10.5px;font-weight:600;color:var(--faint);letter-spacing:.06em;padding:8px 12px 4px">เมนูหลัก</div>
      <?php foreach ($items as [$key, $route, $icon, $label]): ?>
        <a href="<?= h(url($route)) ?>" style="<?= $nav($key) ?>"><span style="flex:none;width:20px;display:grid;place-items:center"><?= $icon ?></span><span data-collapse-hide><?= h($label) ?></span></a>
      <?php endforeach; ?>
      <div data-collapse-hide style="font-size:10.5px;font-weight:600;color:var(--faint);letter-spacing:.06em;padding:14px 12px 4px">ตั้งค่า</div>
      <?php foreach ($settings as [$key, $route, $icon, $label]): ?>
        <a href="<?= h(url($route)) ?>" style="<?= $nav($key) ?>"><span style="flex:none;width:20px;display:grid;place-items:center"><?= $icon ?></span><span data-collapse-hide><?= h($label) ?></span></a>
      <?php endforeach; ?>
    </nav>
    <div style="padding:12px;border-top:1px solid var(--border)">
      <a href="<?= h(url('')) ?>" style="<?= $nav('__none') ?>"><span style="flex:none;width:20px;display:grid;place-items:center">◄</span><span data-collapse-hide>ดูเว็บสาธารณะ</span></a>
    </div>
  </aside>

  <!-- main -->
  <div style="flex:1;min-width:0;display:flex;flex-direction:column">
    <header style="height:64px;flex:none;background:color-mix(in srgb, var(--surface) 90%, transparent);backdrop-filter:blur(8px);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;padding:0 22px;position:sticky;top:0;z-index:20">
      <button data-action="toggle-sidebar" style="width:38px;height:38px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;font-size:15px">☰</button>
      <div style="font-weight:700;font-size:16px"><?= h($title) ?></div>
      <div style="margin-left:auto;display:flex;align-items:center;gap:10px">
        <button data-action="cycle-theme" title="สลับโหมด" style="width:38px;height:38px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;font-size:11.5px;font-weight:600"><span data-theme-glyph>AUTO</span></button>
        <div style="position:relative;padding-left:6px">
          <button type="button" data-action="toggle-user-menu" style="display:flex;align-items:center;gap:9px;padding:4px 8px 4px 12px;border:1px solid var(--border);border-radius:999px;background:var(--surface);color:var(--text);cursor:pointer">
            <span data-collapse-hide style="text-align:right;line-height:1.2"><span style="display:block;font-size:13px;font-weight:600"><?= h($user['name']) ?></span><span style="display:block;font-size:11px;color:var(--muted)"><?= h($user['role']) ?></span></span>
            <span style="width:34px;height:34px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:600;flex:none"><?= h(name_initial($user['name'])) ?></span>
            <span style="font-size:10px;color:var(--muted)">▾</span>
          </button>
          <div data-user-menu class="sc-hidden" style="position:absolute;right:0;top:calc(100% + 8px);width:234px;background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow-lg);overflow:hidden;z-index:50">
            <div style="padding:14px 16px;border-bottom:1px solid var(--border-2)">
              <div style="font-size:13.5px;font-weight:600"><?= h($user['name']) ?></div>
              <div style="font-size:11.5px;color:var(--muted);word-break:break-all"><?= h($user['email']) ?></div>
            </div>
            <a href="<?= h(url('admin/account')) ?>" class="menu-item" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13.5px;color:var(--text);text-decoration:none"><span style="width:18px;text-align:center">🔑</span>เปลี่ยนรหัสผ่าน</a>
            <form method="post" action="<?= h(url('logout')) ?>" style="margin:0;border-top:1px solid var(--border-2)"><?= csrf_field() ?><button type="submit" class="menu-item" style="width:100%;display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13.5px;color:var(--danger);background:none;border:none;cursor:pointer;text-align:left;font-weight:500"><span style="width:18px;text-align:center">⎋</span>ออกจากระบบ</button></form>
          </div>
        </div>
      </div>
    </header>
    <main style="flex:1;padding:26px;overflow-x:hidden">
      <?= $content ?>
    </main>
  </div>
</div>
