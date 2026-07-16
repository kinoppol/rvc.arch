<?php
/** @var string $publicView */
$navBtn = function (bool $active): string {
    return 'height:38px;padding:0 14px;border-radius:9px;border:none;background:'
        . ($active ? 'var(--primary-soft)' : 'transparent') . ';color:'
        . ($active ? 'var(--primary-text)' : 'var(--muted)')
        . ';font-weight:600;font-size:14px;cursor:pointer';
};
$onSearch = in_array($publicView, ['search', 'detail'], true);
?>
<header style="position:sticky;top:0;z-index:40;background:color-mix(in srgb, var(--surface) 88%, transparent);backdrop-filter:blur(10px);border-bottom:1px solid var(--border)">
  <div style="max-width:1200px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;gap:20px">
    <a href="<?= h(url('')) ?>" style="display:flex;align-items:center;gap:11px;text-decoration:none;color:inherit;margin-right:auto">
      <div style="width:38px;height:38px;border-radius:10px;background:var(--primary);color:var(--primary-fg);display:grid;place-items:center;font-weight:700;font-size:17px;box-shadow:var(--shadow)">RV</div>
      <div style="line-height:1.15">
        <div style="font-weight:700;font-size:15px">คลังงานวิจัย</div>
        <div style="font-size:11px;color:var(--muted)">วิทยาลัยอาชีวศึกษาร้อยเอ็ด</div>
      </div>
    </a>
    <nav style="display:flex;gap:4px;align-items:center">
      <a href="<?= h(url('')) ?>" style="<?= $navBtn($publicView === 'home') ?>;display:grid;place-items:center;text-decoration:none">หน้าแรก</a>
      <a href="<?= h(url('search')) ?>" style="<?= $navBtn($onSearch) ?>;display:grid;place-items:center;text-decoration:none">สืบค้นงานวิจัย</a>
    </nav>
    <div style="display:flex;gap:8px;align-items:center">
      <button data-action="cycle-theme" title="สลับโหมดแสดงผล" style="width:38px;height:38px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:grid;place-items:center;font-size:12px;font-weight:600"><span data-theme-glyph>AUTO</span></button>
      <?php if (Auth::check()): ?>
        <a href="<?= h(url(Auth::homeUrl())) ?>" style="height:38px;padding:0 16px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-weight:600;font-size:13.5px;cursor:pointer;display:grid;place-items:center;text-decoration:none"><?= Auth::isAdmin() ? 'ผู้ดูแลระบบ' : 'พื้นที่ของฉัน' ?></a>
      <?php else: ?>
        <a href="<?= h(url('register')) ?>" style="height:38px;padding:0 14px;border-radius:9px;border:none;background:transparent;color:var(--muted);font-weight:600;font-size:13.5px;cursor:pointer;display:grid;place-items:center;text-decoration:none">สมัครสมาชิก</a>
        <a href="<?= h(url('login')) ?>" style="height:38px;padding:0 16px;border-radius:9px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-weight:600;font-size:13.5px;cursor:pointer;display:grid;place-items:center;text-decoration:none">เข้าสู่ระบบ</a>
      <?php endif; ?>
    </div>
  </div>
</header>
