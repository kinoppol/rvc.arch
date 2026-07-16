<?php
/** @var array $list @var string $q @var string $status */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$iconBtn = 'width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--muted);cursor:pointer;font-size:13px;margin-left:5px';
$iconBtnDanger = 'width:32px;height:32px;border-radius:8px;border:1px solid var(--danger-soft);background:var(--danger-soft);color:var(--danger);cursor:pointer;font-size:13px;margin-left:5px';
?>
<div style="animation:fade .25s">
  <form method="get" action="<?= h(url('admin/research')) ?>" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
    <div style="flex:1;min-width:220px;display:flex;align-items:center;gap:8px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:0 14px;box-shadow:var(--shadow)"><span style="color:var(--faint)">⌕</span><input name="q" value="<?= h($q) ?>" placeholder="ค้นหางานวิจัย…" style="flex:1;border:none;background:transparent;color:var(--text);font-size:14px;padding:10px 0;outline:none"/></div>
    <select name="status" onchange="this.form.submit()" style="<?= $inputStyle ?>;max-width:180px;box-shadow:var(--shadow)">
      <option value="">ทุกสถานะ</option>
      <?php foreach (App::STATUSES as $s): ?>
        <option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= h($s) ?></option>
      <?php endforeach; ?>
    </select>
    <a href="<?= h(url('admin/submit')) ?>" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:11px 18px;font-weight:600;font-size:13.5px;cursor:pointer;box-shadow:var(--shadow);text-decoration:none">+ เพิ่มงานวิจัย</a>
  </form>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden">
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:13.5px;min-width:760px">
        <thead><tr style="background:var(--surface-2);text-align:left;color:var(--muted)">
          <th style="padding:13px 18px;font-weight:600">ชื่องานวิจัย</th>
          <th style="padding:13px 12px;font-weight:600">ประเภท</th>
          <th style="padding:13px 12px;font-weight:600">ปี</th>
          <th style="padding:13px 12px;font-weight:600">สถานะ</th>
          <th style="padding:13px 18px;font-weight:600;text-align:right">จัดการ</th>
        </tr></thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr style="border-top:1px solid var(--border-2)">
              <td style="padding:13px 18px;max-width:360px"><div style="font-weight:500;line-height:1.35"><?= h($r['title_th']) ?></div><div style="font-size:11.5px;color:var(--muted)"><?= h($r['leadAuthor']) ?> · <?= h($r['dept']) ?></div></td>
              <td style="padding:13px 12px"><span style="font-size:11.5px;color:<?= h($r['color']) ?>;font-weight:500"><?= h($r['catName']) ?></span></td>
              <td style="padding:13px 12px;color:var(--muted)"><?= (int) $r['pub_year'] ?></td>
              <td style="padding:13px 12px"><span style="font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:999px;background:<?= h($r['statusBg']) ?>;color:<?= h($r['statusFg']) ?>"><?= h($r['status']) ?></span></td>
              <td style="padding:13px 18px;text-align:right;white-space:nowrap">
                <form method="post" action="<?= h(url('admin/research/' . $r['id'] . '/status')) ?>" style="display:inline"><?= csrf_field() ?><button type="submit" title="เปลี่ยนสถานะ" style="<?= $iconBtn ?>">⟳</button></form>
                <a href="<?= h(url('admin/research/' . $r['id'] . '/edit')) ?>" title="แก้ไข" style="<?= $iconBtn ?>;display:inline-grid;place-items:center;text-decoration:none;vertical-align:middle">✎</a>
                <a href="<?= h(url('research/' . $r['id'])) ?>" title="ดู" style="<?= $iconBtn ?>;display:inline-grid;place-items:center;text-decoration:none;vertical-align:middle">👁</a>
                <form method="post" action="<?= h(url('admin/research/' . $r['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('ยืนยันการลบงานวิจัยนี้?')"><?= csrf_field() ?><button type="submit" title="ลบ" style="<?= $iconBtnDanger ?>">🗑</button></form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (!$list): ?>
      <div style="padding:30px;text-align:center;color:var(--muted)">ไม่พบรายการ</div>
    <?php endif; ?>
  </div>
</div>
