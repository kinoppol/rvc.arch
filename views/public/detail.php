<?php
/** @var array $current */
$c = $current;

function dico(string $path, string $size = '16', string $color = 'currentColor'): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="'.$color.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex:none">'.$path.'</svg>';
}
$icoBack     = dico('<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>');
$icoAbstract = dico('<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>');
$icoKeyword  = dico('<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>');
$icoFiles    = dico('<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>');
$icoDownload = dico('<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>');
$icoAuthors  = dico('<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>');
$icoDept     = dico('<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>');
$icoYear     = dico('<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>');
$icoLock     = dico('<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>');
$icoPdf      = dico('<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>');
?>
<div style="max-width:1060px;margin:0 auto;padding:22px 24px 70px">
  <a href="<?= h(url('search')) ?>" style="display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:13.5px;padding:6px 0;font-weight:500;text-decoration:none">
    <?= $icoBack ?> กลับไปยังผลการค้นหา
  </a>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);overflow:hidden;margin-top:8px">

    <!-- Header -->
    <div style="padding:28px 32px;border-bottom:1px solid var(--border);background:linear-gradient(180deg, var(--primary-soft), transparent)">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px">
        <span style="font-size:12px;font-weight:600;color:#fff;background:<?= h($c['color']) ?>;padding:5px 12px;border-radius:999px"><?= h($c['catName']) ?></span>
        <span style="font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:4px"><?= $icoYear ?> ปีที่เผยแพร่ <?= (int) $c['pub_year'] ?> · ปีการศึกษา <?= (int) $c['academic_year'] ?></span>
        <?php if ($c['status'] !== 'เผยแพร่'): ?>
          <span style="font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:999px;background:<?= h($c['statusBg']) ?>;color:<?= h($c['statusFg']) ?>"><?= h($c['status']) ?> (ตัวอย่างสำหรับผู้ดูแล)</span>
        <?php endif; ?>
      </div>
      <h1 style="font-size:26px;line-height:1.35;font-weight:700;margin:0 0 6px;text-wrap:balance"><?= h($c['title_th']) ?></h1>
      <div style="font-size:15px;color:var(--muted);font-style:italic"><?= h($c['title_en']) ?></div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:0">

      <!-- Main content -->
      <div style="padding:28px 32px;min-width:0">

        <!-- Abstract TH -->
        <h3 style="font-size:15px;font-weight:700;margin:0 0 10px;color:var(--primary-text);display:flex;align-items:center;gap:7px">
          <?= $icoAbstract ?> บทคัดย่อ
        </h3>
        <p style="font-size:14.5px;line-height:1.8;margin:0 0 20px;text-wrap:pretty"><?= nl2br(h($c['abstract_th'])) ?></p>

        <!-- Abstract EN -->
        <?php if (trim((string) $c['abstract_en']) !== ''): ?>
          <h3 style="font-size:14px;font-weight:700;margin:0 0 8px;color:var(--muted);display:flex;align-items:center;gap:7px">
            <?= dico('<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>','15','var(--muted)') ?> Abstract
          </h3>
          <p style="font-size:13.5px;line-height:1.7;margin:0 0 24px;color:var(--muted);text-wrap:pretty"><?= nl2br(h($c['abstract_en'])) ?></p>
        <?php endif; ?>

        <!-- Keywords -->
        <?php if ($c['keywords']): ?>
          <h3 style="font-size:14px;font-weight:700;margin:0 0 10px;display:flex;align-items:center;gap:7px">
            <?= $icoKeyword ?> คำสำคัญ
          </h3>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:26px">
            <?php foreach ($c['keywords'] as $k): ?>
              <span style="font-size:12.5px;background:var(--surface-3);color:var(--text);padding:5px 12px;border-radius:8px"><?= h($k) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Files -->
        <h3 style="font-size:15px;font-weight:700;margin:0 0 6px;display:flex;align-items:center;gap:7px">
          <?= $icoFiles ?> เอกสารงานวิจัย
        </h3>
        <div style="font-size:12.5px;color:var(--muted);margin-bottom:12px">เปิดเผยเฉพาะบางส่วนตามที่ผู้จัดทำอนุญาต</div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach ($c['files'] as $f): ?>
            <?php $open = $f['uploaded'] && $f['is_public']; ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2)">
              <span style="width:36px;height:36px;border-radius:8px;display:grid;place-items:center;background:<?= $open ? 'var(--danger-soft)' : 'var(--surface-3)' ?>;color:<?= $open ? 'var(--danger)' : 'var(--faint)' ?>">
                <?= $icoPdf ?>
              </span>
              <div style="flex:1;min-width:0">
                <div style="font-size:13.5px;font-weight:500"><?= h($f['chapter_name']) ?></div>
                <div style="font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:4px;margin-top:2px">
                  <?php if (!$f['uploaded']): ?>
                    ยังไม่มีไฟล์
                  <?php elseif ($f['is_public']): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span style="color:#22c55e;font-weight:500">เปิดเผยสาธารณะ</span>
                  <?php else: ?>
                    <?= dico('<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>','11','var(--faint)') ?>
                    เฉพาะภายใน
                  <?php endif; ?>
                </div>
              </div>
              <?php if ($open): ?>
                <a href="<?= h(url('download/' . (int) $f['id'])) ?>" style="display:inline-flex;align-items:center;gap:6px;background:var(--primary-soft);color:var(--primary-text);border:none;border-radius:8px;padding:7px 14px;font-weight:600;font-size:12.5px;cursor:pointer;text-decoration:none">
                  <?= $icoDownload ?> ดาวน์โหลด
                </a>
              <?php elseif ($f['uploaded'] && Auth::check()): ?>
                <a href="<?= h(url('download/' . (int) $f['id'])) ?>" style="font-size:12px;color:var(--faint);display:inline-flex;align-items:center;gap:5px;text-decoration:none">
                  <?= $icoLock ?> ดู (ผู้ดูแล)
                </a>
              <?php else: ?>
                <span style="font-size:12px;color:var(--faint);display:inline-flex;align-items:center;gap:5px">
                  <?= $icoLock ?> ไม่เปิดเผย
                </span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Sidebar -->
      <aside style="padding:28px;border-left:1px solid var(--border);background:var(--surface-2)">
        <h3 style="font-size:14px;font-weight:700;margin:0 0 12px;display:flex;align-items:center;gap:7px">
          <?= $icoAuthors ?> ผู้จัดทำ
        </h3>
        <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px">
          <?php foreach ($c['authors'] as $a): ?>
            <div style="display:flex;gap:10px;align-items:center">
              <span style="width:34px;height:34px;border-radius:50%;background:var(--primary-soft);color:var(--primary-text);display:grid;place-items:center;font-weight:600;font-size:13px;flex:none"><?= h($a['initial']) ?></span>
              <div style="min-width:0">
                <div style="font-size:13.5px;font-weight:500;line-height:1.3"><?= h($a['name']) ?></div>
                <div style="font-size:11.5px;color:var(--muted)"><?= h($a['role']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <dl style="margin:0;font-size:13px;display:flex;flex-direction:column;gap:14px">
          <div>
            <dt style="color:var(--muted);font-size:11.5px;margin-bottom:4px;display:flex;align-items:center;gap:5px"><?= $icoDept ?> สาขาวิชา/แผนกวิชา</dt>
            <dd style="margin:0;font-weight:500"><?= h($c['dept']) ?></dd>
          </div>
          <div>
            <dt style="color:var(--muted);font-size:11.5px;margin-bottom:4px;display:flex;align-items:center;gap:5px"><?= $icoYear ?> ปีการศึกษา</dt>
            <dd style="margin:0;font-weight:500"><?= (int) $c['academic_year'] ?></dd>
          </div>
          <div>
            <dt style="color:var(--muted);font-size:11.5px;margin-bottom:4px;display:flex;align-items:center;gap:5px"><?= $icoYear ?> ปีที่เผยแพร่</dt>
            <dd style="margin:0;font-weight:500"><?= (int) $c['pub_year'] ?></dd>
          </div>
        </dl>
      </aside>
    </div>
  </div>
</div>
