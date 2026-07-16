<?php
/** @var array $counts @var array $catBars @var array $yearBars @var array $pending */
$stat = [
    ['งานวิจัยทั้งหมด', $counts['total'],     '≣', 'var(--primary-soft)', 'var(--primary-text)'],
    ['เผยแพร่แล้ว',     $counts['published'], '✓', 'var(--ok-soft)',      'var(--ok)'],
    ['รอตรวจสอบ',       $counts['pending'],   '⏳', 'var(--warn-soft)',    'var(--warn)'],
    ['ผู้ใช้งาน',       $counts['users'],     '◍', 'var(--info-soft)',    'var(--info)'],
];
?>
<div style="animation:fade .25s">
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:24px">
    <?php foreach ($stat as [$label, $value, $icon, $bg, $fg]): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start"><div style="font-size:13px;color:var(--muted);font-weight:500"><?= h($label) ?></div><span style="width:32px;height:32px;border-radius:9px;background:<?= $bg ?>;color:<?= $fg ?>;display:grid;place-items:center;font-weight:700;font-size:14px"><?= $icon ?></span></div>
        <div style="font-size:30px;font-weight:700;margin-top:8px"><?= (int) $value ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1.3fr 1fr;gap:18px;margin-bottom:18px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
      <div style="font-weight:600;font-size:15px;margin-bottom:18px">จำนวนงานวิจัยแยกตามประเภท</div>
      <div style="display:flex;flex-direction:column;gap:15px">
        <?php foreach ($catBars as $b): ?>
          <div>
            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px"><span style="color:var(--text)"><?= h($b['name']) ?></span><span style="color:var(--muted);font-weight:600"><?= (int) $b['count'] ?></span></div>
            <div style="height:9px;background:var(--surface-3);border-radius:99px;overflow:hidden"><div style="height:100%;width:<?= h($b['pct']) ?>;background:<?= h($b['color']) ?>;border-radius:99px;transition:width .5s"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
      <div style="font-weight:600;font-size:15px;margin-bottom:18px">แยกตามปีที่เผยแพร่</div>
      <div style="display:flex;align-items:flex-end;gap:14px;height:150px;padding-top:10px">
        <?php foreach ($yearBars as $y): ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;height:100%;justify-content:flex-end">
            <div style="font-size:12px;font-weight:600;color:var(--muted)"><?= (int) $y['count'] ?></div>
            <div style="width:100%;height:<?= h($y['pct']) ?>;background:linear-gradient(180deg,var(--primary),color-mix(in srgb,var(--primary) 60%, transparent));border-radius:7px 7px 0 0;min-height:6px;transition:height .5s"></div>
            <div style="font-size:11.5px;color:var(--muted)"><?= (int) $y['year'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden">
    <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center"><div style="font-weight:600;font-size:15px">รายการรอตรวจสอบ</div><span style="font-size:12px;font-weight:600;color:var(--warn);background:var(--warn-soft);padding:4px 11px;border-radius:999px"><?= count($pending) ?> รายการ</span></div>
    <div>
      <?php foreach ($pending as $r): ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 22px;border-bottom:1px solid var(--border-2)">
          <div style="flex:1;min-width:0"><div style="font-size:14px;font-weight:500"><?= h($r['title_th']) ?></div><div style="font-size:12px;color:var(--muted)"><?= h($r['catName']) ?> · <?= h($r['leadAuthor']) ?> · <?= h($r['dept']) ?></div></div>
          <form method="post" action="<?= h(url('admin/research/' . $r['id'] . '/approve')) ?>" style="margin:0"><?= csrf_field() ?><button type="submit" style="background:var(--ok);color:#fff;border:none;border-radius:8px;padding:7px 15px;font-weight:600;font-size:12.5px;cursor:pointer">อนุมัติเผยแพร่</button></form>
          <a href="<?= h(url('research/' . $r['id'])) ?>" style="background:var(--surface-3);color:var(--text);border:none;border-radius:8px;padding:7px 13px;font-weight:600;font-size:12.5px;cursor:pointer;text-decoration:none">ดู</a>
        </div>
      <?php endforeach; ?>
      <?php if (!$pending): ?>
        <div style="padding:26px;text-align:center;color:var(--muted);font-size:13.5px">ไม่มีรายการรอตรวจสอบ ✓</div>
      <?php endif; ?>
    </div>
  </div>
</div>
