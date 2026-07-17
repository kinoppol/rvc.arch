<?php
/** @var array $filters @var array $results @var array $enabledCats @var array $depts @var array $years */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$f = $filters;
?>
<div style="width:100%;max-width:1600px;margin:0 auto;padding:28px clamp(24px,3vw,56px) 70px">
  <h1 style="font-size:26px;font-weight:700;margin:0 0 4px">สืบค้นงานวิจัยขั้นสูง</h1>
  <p style="color:var(--muted);margin:0 0 22px;font-size:14px">ค้นจากชื่อเรื่อง ผู้จัดทำ คำสำคัญ และบทคัดย่อ พร้อมตัวกรองและการเรียงลำดับ</p>

  <form method="get" action="<?= h(url('search')) ?>" id="searchForm" style="display:grid;grid-template-columns:270px 1fr;gap:26px;align-items:start">
    <!-- filters -->
    <aside style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow);position:sticky;top:80px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
        <div style="font-weight:600;font-size:14.5px">ตัวกรอง</div>
        <a href="<?= h(url('search')) ?>" style="color:var(--primary-text);font-weight:600;font-size:12.5px;text-decoration:none">ล้าง</a>
      </div>
      <label style="font-size:12.5px;font-weight:500;color:var(--muted);display:block;margin-bottom:6px">ประเภทงานวิจัย</label>
      <select name="cat" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>">
        <option value="">ทุกประเภท</option>
        <?php foreach ($enabledCats as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= (string) $f['cat'] === (string) $c['id'] ? 'selected' : '' ?>><?= h($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label style="font-size:12.5px;font-weight:500;color:var(--muted);display:block;margin:14px 0 6px">สาขาวิชา/แผนกวิชา</label>
      <select name="dept" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>">
        <option value="">ทุกสาขา</option>
        <?php foreach ($depts as $d): ?>
          <option value="<?= h($d) ?>" <?= $f['dept'] === $d ? 'selected' : '' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
      <label style="font-size:12.5px;font-weight:500;color:var(--muted);display:block;margin:14px 0 6px">ปีที่เผยแพร่</label>
      <select name="year" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>">
        <option value="">ทุกปี</option>
        <?php foreach ($years as $y): ?>
          <option value="<?= (int) $y ?>" <?= (string) $f['year'] === (string) $y ? 'selected' : '' ?>><?= (int) $y ?></option>
        <?php endforeach; ?>
      </select>
    </aside>

    <!-- results -->
    <div>
      <div style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap">
        <div style="flex:1;min-width:220px;display:flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:0 14px;box-shadow:var(--shadow)">
          <span style="color:var(--faint);font-size:15px">⌕</span>
          <input name="q" value="<?= h($f['q']) ?>" placeholder="พิมพ์คำค้น…" style="flex:1;border:none;background:transparent;color:var(--text);font-size:14.5px;padding:11px 0;outline:none"/>
        </div>
        <select name="sort" onchange="document.getElementById('searchForm').submit()" style="<?= $inputStyle ?>;max-width:190px;box-shadow:var(--shadow)">
          <option value="new" <?= $f['sort'] === 'new' ? 'selected' : '' ?>>ใหม่สุดก่อน</option>
          <option value="old" <?= $f['sort'] === 'old' ? 'selected' : '' ?>>เก่าสุดก่อน</option>
          <option value="az"  <?= $f['sort'] === 'az'  ? 'selected' : '' ?>>ชื่อเรื่อง ก-ฮ</option>
        </select>
        <button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:0 20px;font-weight:600;font-size:14px;cursor:pointer;box-shadow:var(--shadow)">ค้นหา</button>
      </div>
      <div style="font-size:13px;color:var(--muted);margin-bottom:12px">พบ <b style="color:var(--text)"><?= count($results) ?></b> รายการ</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(420px,1fr));gap:12px">
        <?php foreach ($results as $r): ?>
          <a href="<?= h(url('research/' . $r['id'])) ?>" class="card-hover card-outline" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px 20px;box-shadow:var(--shadow);text-decoration:none;color:inherit;display:flex;gap:16px;align-items:flex-start">
            <div style="width:46px;height:46px;border-radius:10px;background:color-mix(in srgb, <?= h($r['color']) ?> 14%, transparent);color:<?= h($r['color']) ?>;display:grid;place-items:center;font-weight:700;flex:none"><?= h($r['pubYearShort']) ?></div>
            <div style="flex:1;min-width:0">
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:5px">
                <span style="font-size:11px;font-weight:600;color:<?= h($r['color']) ?>"><?= h($r['catName']) ?></span>
                <span style="color:var(--faint)">·</span>
                <span style="font-size:11.5px;color:var(--muted)"><?= h($r['dept']) ?></span>
              </div>
              <div style="font-weight:600;font-size:15.5px;line-height:1.4"><?= h($r['title_th']) ?></div>
              <div style="font-size:12.5px;color:var(--muted);margin-top:3px"><?= h($r['title_en']) ?></div>
              <div style="font-size:12.5px;color:var(--muted);margin-top:8px"><?= h($r['authorsLine']) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if (!$results): ?>
          <div style="grid-column:1/-1;text-align:center;padding:50px 20px;color:var(--muted);background:var(--surface);border:1px dashed var(--border);border-radius:14px">ไม่พบงานวิจัยที่ตรงกับเงื่อนไข ลองปรับคำค้นหรือตัวกรอง</div>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>
