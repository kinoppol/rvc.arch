<?php
/** @var array $current */
$c = $current;
?>
<div style="max-width:1060px;margin:0 auto;padding:22px 24px 70px">
  <a href="<?= h(url('search')) ?>" style="color:var(--muted);font-size:13.5px;padding:6px 0;font-weight:500;text-decoration:none;display:inline-block">← กลับไปยังผลการค้นหา</a>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);overflow:hidden;margin-top:8px">
    <div style="padding:28px 32px;border-bottom:1px solid var(--border);background:linear-gradient(180deg, var(--primary-soft), transparent)">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px">
        <span style="font-size:12px;font-weight:600;color:#fff;background:<?= h($c['color']) ?>;padding:5px 12px;border-radius:999px"><?= h($c['catName']) ?></span>
        <span style="font-size:12.5px;color:var(--muted)">ปีที่เผยแพร่ <?= (int) $c['pub_year'] ?> · ปีการศึกษา <?= (int) $c['academic_year'] ?></span>
        <?php if ($c['status'] !== 'เผยแพร่'): ?>
          <span style="font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:999px;background:<?= h($c['statusBg']) ?>;color:<?= h($c['statusFg']) ?>"><?= h($c['status']) ?> (ตัวอย่างสำหรับผู้ดูแล)</span>
        <?php endif; ?>
      </div>
      <h1 style="font-size:26px;line-height:1.35;font-weight:700;margin:0 0 6px;text-wrap:balance"><?= h($c['title_th']) ?></h1>
      <div style="font-size:15px;color:var(--muted);font-style:italic"><?= h($c['title_en']) ?></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 300px;gap:0">
      <div style="padding:28px 32px;min-width:0">
        <h3 style="font-size:15px;font-weight:700;margin:0 0 8px;color:var(--primary-text)">บทคัดย่อ</h3>
        <p style="font-size:14.5px;line-height:1.75;margin:0 0 18px;text-wrap:pretty"><?= nl2br(h($c['abstract_th'])) ?></p>
        <?php if (trim((string) $c['abstract_en']) !== ''): ?>
          <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;color:var(--muted)">Abstract</h3>
          <p style="font-size:13.5px;line-height:1.7;margin:0 0 22px;color:var(--muted);text-wrap:pretty"><?= nl2br(h($c['abstract_en'])) ?></p>
        <?php endif; ?>
        <?php if ($c['keywords']): ?>
          <h3 style="font-size:14px;font-weight:700;margin:0 0 10px">คำสำคัญ</h3>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:26px">
            <?php foreach ($c['keywords'] as $k): ?>
              <span style="font-size:12.5px;background:var(--surface-3);color:var(--text);padding:5px 12px;border-radius:8px"><?= h($k) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <h3 style="font-size:15px;font-weight:700;margin:0 0 12px">เอกสารงานวิจัย</h3>
        <div style="font-size:12.5px;color:var(--muted);margin-bottom:12px">เปิดเผยเฉพาะบางส่วนตามที่ผู้จัดทำอนุญาต</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach ($c['files'] as $f): ?>
            <?php $open = $f['uploaded'] && $f['is_public']; ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2)">
              <span style="width:34px;height:34px;border-radius:8px;display:grid;place-items:center;font-size:11px;font-weight:700;background:<?= $open ? 'var(--danger-soft)' : 'var(--surface-3)' ?>;color:<?= $open ? 'var(--danger)' : 'var(--faint)' ?>">PDF</span>
              <div style="flex:1;min-width:0">
                <div style="font-size:13.5px;font-weight:500"><?= h($f['chapter_name']) ?></div>
                <div style="font-size:11.5px;color:var(--muted)"><?= $f['uploaded'] ? ($f['is_public'] ? 'PDF · เปิดเผยสาธารณะ' : 'PDF · เฉพาะภายใน') : 'ยังไม่มีไฟล์' ?></div>
              </div>
              <?php if ($open): ?>
                <a href="<?= h(url('download/' . (int) $f['id'])) ?>" style="background:var(--primary-soft);color:var(--primary-text);border:none;border-radius:8px;padding:7px 14px;font-weight:600;font-size:12.5px;cursor:pointer;text-decoration:none">ดาวน์โหลด</a>
              <?php elseif ($f['uploaded'] && Auth::check()): ?>
                <a href="<?= h(url('download/' . (int) $f['id'])) ?>" style="font-size:12px;color:var(--faint);display:flex;align-items:center;gap:5px;text-decoration:none">🔒 ดู (ผู้ดูแล)</a>
              <?php else: ?>
                <span style="font-size:12px;color:var(--faint);display:flex;align-items:center;gap:5px">🔒 ไม่เปิดเผย</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <aside style="padding:28px 28px;border-left:1px solid var(--border);background:var(--surface-2)">
        <h3 style="font-size:14px;font-weight:700;margin:0 0 12px">ผู้จัดทำ</h3>
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px">
          <?php foreach ($c['authors'] as $a): ?>
            <div style="display:flex;gap:10px;align-items:center">
              <span style="width:34px;height:34px;border-radius:50%;background:var(--primary-soft);color:var(--primary-text);display:grid;place-items:center;font-weight:600;font-size:13px;flex:none"><?= h($a['initial']) ?></span>
              <div style="min-width:0"><div style="font-size:13.5px;font-weight:500;line-height:1.3"><?= h($a['name']) ?></div><div style="font-size:11.5px;color:var(--muted)"><?= h($a['role']) ?></div></div>
            </div>
          <?php endforeach; ?>
        </div>
        <dl style="margin:0;font-size:13px;display:flex;flex-direction:column;gap:12px">
          <div><dt style="color:var(--muted);font-size:11.5px;margin-bottom:2px">สาขาวิชา/แผนกวิชา</dt><dd style="margin:0;font-weight:500"><?= h($c['dept']) ?></dd></div>
          <div><dt style="color:var(--muted);font-size:11.5px;margin-bottom:2px">ปีการศึกษา</dt><dd style="margin:0;font-weight:500"><?= (int) $c['academic_year'] ?></dd></div>
          <div><dt style="color:var(--muted);font-size:11.5px;margin-bottom:2px">ปีที่เผยแพร่</dt><dd style="margin:0;font-weight:500"><?= (int) $c['pub_year'] ?></dd></div>
        </dl>
      </aside>
    </div>
  </div>
</div>
