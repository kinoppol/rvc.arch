<?php
/** @var string $content @var string $memberView @var string $title */
$user = Auth::user() ?? ['name' => '', 'email' => '', 'role' => ''];
$navBtn = function (bool $active): string {
    return 'height:38px;padding:0 14px;border-radius:9px;border:none;background:'
        . ($active ? 'var(--primary-soft)' : 'transparent') . ';color:'
        . ($active ? 'var(--primary-text)' : 'var(--muted)')
        . ';font-weight:600;font-size:14px;cursor:pointer;display:grid;place-items:center;text-decoration:none';
};
?>
<div style="display:flex;flex-direction:column;min-height:100vh">
  <header style="position:sticky;top:0;z-index:40;background:color-mix(in srgb, var(--surface) 90%, transparent);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)">
    <div style="max-width:1080px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;gap:18px">
      <a href="<?= h(url('my')) ?>" style="display:flex;align-items:center;gap:11px;text-decoration:none;color:inherit;margin-right:auto">
        <div style="width:36px;height:36px;border-radius:9px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;flex:none">RV</div>
        <div style="line-height:1.15"><div style="font-weight:700;font-size:14px">พื้นที่สมาชิก</div><div style="font-size:11px;color:var(--muted)">คลังงานวิจัย</div></div>
      </a>
      <nav style="display:flex;gap:4px;align-items:center">
        <a href="<?= h(url('my')) ?>" style="<?= $navBtn($memberView === 'home') ?>">งานวิจัยของฉัน</a>
        <a href="<?= h(url('my/submit')) ?>" style="<?= $navBtn($memberView === 'submit') ?>">ส่งงานวิจัย</a>
      </nav>
      <button data-action="cycle-theme" title="สลับโหมดแสดงผล" style="width:38px;height:38px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:grid;place-items:center;font-size:12px;font-weight:600"><span data-theme-glyph>AUTO</span></button>
      <div style="position:relative">
        <button type="button" data-action="toggle-user-menu" style="display:flex;align-items:center;gap:9px;padding:4px 8px 4px 12px;border:1px solid var(--border);border-radius:999px;background:var(--surface);color:var(--text);cursor:pointer">
          <span style="text-align:right;line-height:1.2"><span style="display:block;font-size:13px;font-weight:600"><?= h($user['name']) ?></span><span style="display:block;font-size:11px;color:var(--muted)"><?= h($user['role']) ?></span></span>
          <span style="width:34px;height:34px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:600;flex:none"><?= h(name_initial($user['name'])) ?></span>
          <span style="font-size:10px;color:var(--muted)">▾</span>
        </button>
        <div data-user-menu class="sc-hidden" style="position:absolute;right:0;top:calc(100% + 8px);width:234px;background:var(--surface);border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow-lg);overflow:hidden;z-index:50">
          <div style="padding:14px 16px;border-bottom:1px solid var(--border-2)">
            <div style="font-size:13.5px;font-weight:600"><?= h($user['name']) ?></div>
            <div style="font-size:11.5px;color:var(--muted);word-break:break-all"><?= h($user['email']) ?></div>
          </div>
          <a href="<?= h(url('account')) ?>" class="menu-item" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13.5px;color:var(--text);text-decoration:none"><span style="width:18px;text-align:center">🔑</span>เปลี่ยนรหัสผ่าน</a>
          <a href="<?= h(url('')) ?>" class="menu-item" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13.5px;color:var(--text);text-decoration:none"><span style="width:18px;text-align:center">◄</span>ดูเว็บสาธารณะ</a>
          <form method="post" action="<?= h(url('logout')) ?>" style="margin:0;border-top:1px solid var(--border-2)"><?= csrf_field() ?><button type="submit" class="menu-item" style="width:100%;display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:13.5px;color:var(--danger);background:none;border:none;cursor:pointer;text-align:left;font-weight:500"><span style="width:18px;text-align:center">⎋</span>ออกจากระบบ</button></form>
        </div>
      </div>
    </div>
  </header>
  <main style="flex:1;max-width:1080px;width:100%;margin:0 auto;padding:26px 24px 60px">
    <div style="font-weight:700;font-size:20px;margin-bottom:18px"><?= h($title) ?></div>
    <?= $content ?>
  </main>
</div>
