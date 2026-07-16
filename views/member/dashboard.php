<?php
/** @var array $list */
$editable = ['แบบร่าง', 'รอตรวจสอบ'];
?>
<div style="animation:fade .25s">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <p style="margin:0;color:var(--muted);font-size:13.5px">งานวิจัยที่คุณส่งเข้าระบบ · แก้ไขได้เฉพาะสถานะ “แบบร่าง” และ “รอตรวจสอบ”</p>
    <a href="<?= h(url('my/submit')) ?>" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:11px 18px;font-weight:600;font-size:13.5px;text-decoration:none;box-shadow:var(--shadow)">+ ส่งงานวิจัยใหม่</a>
  </div>

  <?php if (!$list): ?>
    <div style="text-align:center;padding:50px 20px;color:var(--muted);background:var(--surface);border:1px dashed var(--border);border-radius:14px">
      คุณยังไม่มีงานวิจัยในระบบ — เริ่มต้นด้วยการ <a class="lnk" href="<?= h(url('my/submit')) ?>">ส่งงานวิจัยใหม่</a>
    </div>
  <?php else: ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden">
      <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13.5px;min-width:640px">
          <thead><tr style="background:var(--surface-2);text-align:left;color:var(--muted)">
            <th style="padding:13px 18px;font-weight:600">ชื่องานวิจัย</th>
            <th style="padding:13px 12px;font-weight:600">ประเภท</th>
            <th style="padding:13px 12px;font-weight:600">ปี</th>
            <th style="padding:13px 12px;font-weight:600">สถานะ</th>
            <th style="padding:13px 18px;font-weight:600;text-align:right">จัดการ</th>
          </tr></thead>
          <tbody>
            <?php foreach ($list as $r): ?>
              <?php $canEdit = in_array($r['status'], $editable, true); ?>
              <tr style="border-top:1px solid var(--border-2)">
                <td style="padding:13px 18px;max-width:360px"><div style="font-weight:500;line-height:1.35"><?= h($r['title_th']) ?></div><div style="font-size:11.5px;color:var(--muted)"><?= h($r['dept'] ?: '—') ?></div></td>
                <td style="padding:13px 12px"><span style="font-size:11.5px;color:<?= h($r['color']) ?>;font-weight:500"><?= h($r['catName']) ?></span></td>
                <td style="padding:13px 12px;color:var(--muted)"><?= (int) $r['pub_year'] ?: '—' ?></td>
                <td style="padding:13px 12px"><span style="font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:999px;background:<?= h($r['statusBg']) ?>;color:<?= h($r['statusFg']) ?>"><?= h($r['status']) ?></span></td>
                <td style="padding:13px 18px;text-align:right;white-space:nowrap">
                  <?php if ($r['status'] === 'เผยแพร่'): ?>
                    <a href="<?= h(url('research/' . $r['id'])) ?>" style="font-size:12.5px;color:var(--primary-text);text-decoration:none;font-weight:600">ดูหน้าเผยแพร่</a>
                  <?php elseif ($canEdit): ?>
                    <a href="<?= h(url('my/research/' . $r['id'] . '/edit')) ?>" style="font-size:12.5px;color:var(--primary-text);text-decoration:none;font-weight:600;margin-right:10px">แก้ไข</a>
                    <form method="post" action="<?= h(url('my/research/' . $r['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('ยืนยันการลบงานวิจัยนี้?')"><?= csrf_field() ?><button type="submit" style="font-size:12.5px;color:var(--danger);background:none;border:none;cursor:pointer;font-weight:600">ลบ</button></form>
                  <?php else: ?>
                    <span style="font-size:12px;color:var(--faint)">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
