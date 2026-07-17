<?php
/** @var array $filters @var array $results @var array $enabledCats @var array $depts @var array $years */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px 10px 36px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none;appearance:none;-webkit-appearance:none';
$f = $filters;

/* ── inline SVG helpers ─────────────────────────────────────── */
function ico(string $path, string $size = '15', string $extra = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;flex:none'.(strlen($extra)?';'.$extra:'').'">'.$path.'</svg>';
}
$icoFunnel  = ico('<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>');
$icoSearch  = ico('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>');
$icoSearch18= ico('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>','18');
$icoCalendar= ico('<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>');
$icoBuilding= ico('<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>');
$icoTag     = ico('<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>');
$icoX       = ico('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>','14');
$icoSort    = ico('<line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/>');
$icoDoc     = ico('<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>','14');
$icoUser    = ico('<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>','13');
?>
<div style="width:100%;max-width:1600px;margin:0 auto;padding:28px clamp(24px,3vw,56px) 70px">
  <h1 style="font-size:26px;font-weight:700;margin:0 0 4px">สืบค้นงานวิจัยขั้นสูง</h1>
  <p style="color:var(--muted);margin:0 0 22px;font-size:14px">ค้นจากชื่อเรื่อง ผู้จัดทำ คำสำคัญ และบทคัดย่อ พร้อมตัวกรองและการเรียงลำดับ</p>

  <form method="get" action="<?= h(url('search')) ?>" id="searchForm" style="display:grid;grid-template-columns:270px 1fr;gap:26px;align-items:start">

    <!-- ── Filter sidebar ── -->
    <aside style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow);position:sticky;top:80px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <div style="font-weight:700;font-size:14.5px;display:flex;align-items:center;gap:7px;color:var(--text)">
          <span style="color:var(--primary-text)"><?= $icoFunnel ?></span> ตัวกรอง
        </div>
        <a href="<?= h(url('search')) ?>" style="display:flex;align-items:center;gap:4px;color:var(--muted);font-size:12px;text-decoration:none;padding:4px 8px;border-radius:7px;border:1px solid var(--border);background:var(--surface-2)"><?= $icoX ?> ล้าง</a>
      </div>

      <!-- ประเภท -->
      <label style="font-size:12px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:5px;margin-bottom:6px">
        <span style="color:var(--primary-text)"><?= $icoTag ?></span> ประเภทงานวิจัย
      </label>
      <div style="position:relative;margin-bottom:14px">
        <select name="cat" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>">
          <option value="">ทุกประเภท</option>
          <?php foreach ($enabledCats as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (string) $f['cat'] === (string) $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--faint);pointer-events:none"><?= $icoTag ?></span>
      </div>

      <!-- สาขา -->
      <label style="font-size:12px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:5px;margin-bottom:6px">
        <span style="color:var(--primary-text)"><?= $icoBuilding ?></span> สาขาวิชา/แผนกวิชา
      </label>
      <div style="position:relative;margin-bottom:14px">
        <select name="dept" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>">
          <option value="">ทุกสาขา</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= h($d) ?>" <?= $f['dept'] === $d ? 'selected' : '' ?>><?= h($d) ?></option>
          <?php endforeach; ?>
        </select>
        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--faint);pointer-events:none"><?= $icoBuilding ?></span>
      </div>

      <!-- ปี -->
      <label style="font-size:12px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:5px;margin-bottom:6px">
        <span style="color:var(--primary-text)"><?= $icoCalendar ?></span> ปีที่เผยแพร่
      </label>
      <div style="position:relative">
        <select name="year" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>">
          <option value="">ทุกปี</option>
          <?php foreach ($years as $y): ?>
            <option value="<?= (int) $y ?>" <?= (string) $f['year'] === (string) $y ? 'selected' : '' ?>><?= (int) $y ?></option>
          <?php endforeach; ?>
        </select>
        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--faint);pointer-events:none"><?= $icoCalendar ?></span>
      </div>
    </aside>

    <!-- ── Results ── -->
    <div>
      <!-- Search bar row -->
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <div style="flex:1;min-width:220px;display:flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:0 14px;box-shadow:var(--shadow)">
          <span style="color:var(--faint);display:flex;align-items:center"><?= $icoSearch ?></span>
          <input name="q" value="<?= h($f['q']) ?>" placeholder="พิมพ์คำค้น…" style="flex:1;border:none;background:transparent;color:var(--text);font-size:14.5px;padding:11px 0;outline:none"/>
        </div>
        <div style="position:relative;max-width:200px">
          <select name="sort" onchange="document.getElementById('searchForm').submit()" style="width:100%;border:1px solid var(--border);border-radius:10px;padding:0 12px 0 36px;font-size:14px;background:var(--surface);color:var(--text);outline:none;appearance:none;-webkit-appearance:none;height:44px;box-shadow:var(--shadow);cursor:pointer">
            <option value="new" <?= $f['sort'] === 'new' ? 'selected' : '' ?>>ใหม่สุดก่อน</option>
            <option value="old" <?= $f['sort'] === 'old' ? 'selected' : '' ?>>เก่าสุดก่อน</option>
            <option value="az"  <?= $f['sort'] === 'az'  ? 'selected' : '' ?>>ชื่อเรื่อง ก-ฮ</option>
          </select>
          <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--faint);pointer-events:none"><?= $icoSort ?></span>
        </div>
        <button type="submit" style="display:flex;align-items:center;gap:7px;background:var(--primary);color:#fff;border:none;border-radius:10px;padding:0 20px;font-weight:600;font-size:14px;cursor:pointer;box-shadow:var(--shadow);height:44px">
          <?= $icoSearch18 ?> ค้นหา
        </button>
      </div>

      <!-- Count -->
      <div style="font-size:13px;color:var(--muted);margin-bottom:12px;display:flex;align-items:center;gap:5px">
        <?= $icoDoc ?> พบ <b style="color:var(--text);margin:0 2px"><?= count($results) ?></b> รายการ
      </div>

      <!-- Cards grid -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(420px,1fr));gap:12px">
        <?php foreach ($results as $r): ?>
          <a href="<?= h(url('research/' . $r['id'])) ?>" class="card-hover card-outline" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px 20px;box-shadow:var(--shadow);text-decoration:none;color:inherit;display:flex;gap:14px;align-items:flex-start">
            <!-- year badge -->
            <div style="width:46px;height:46px;border-radius:10px;background:color-mix(in srgb, <?= h($r['color']) ?> 14%, transparent);color:<?= h($r['color']) ?>;display:grid;place-items:center;font-weight:700;flex:none;font-size:13px"><?= h($r['pubYearShort']) ?></div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:5px">
                <span style="font-size:11px;font-weight:600;color:<?= h($r['color']) ?>"><?= h($r['catName']) ?></span>
                <span style="color:var(--faint)">·</span>
                <span style="font-size:11.5px;color:var(--muted)"><?= h($r['dept']) ?></span>
              </div>
              <div style="font-weight:600;font-size:15px;line-height:1.4;display:flex;align-items:flex-start;gap:6px">
                <span style="color:<?= h($r['color']) ?>;margin-top:2px;flex:none"><?= $icoDoc ?></span>
                <span><?= h($r['title_th']) ?></span>
              </div>
              <?php if (trim((string) $r['title_en']) !== ''): ?>
                <div style="font-size:12px;color:var(--muted);margin-top:2px;font-style:italic"><?= h($r['title_en']) ?></div>
              <?php endif; ?>
              <div style="font-size:12px;color:var(--muted);margin-top:7px;display:flex;align-items:center;gap:5px">
                <span style="color:var(--faint)"><?= $icoUser ?></span>
                <?= h($r['authorsLine']) ?>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if (!$results): ?>
          <div style="grid-column:1/-1;text-align:center;padding:50px 20px;color:var(--muted);background:var(--surface);border:1px dashed var(--border);border-radius:14px">
            <div style="margin-bottom:10px;opacity:.4"><?= ico('<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>','40') ?></div>
            ไม่พบงานวิจัยที่ตรงกับเงื่อนไข ลองปรับคำค้นหรือตัวกรอง
          </div>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>
