<?php
/** @var array $cats */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$iconBtn = 'width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--muted);cursor:pointer;font-size:13px;margin-left:5px';
$iconBtnDanger = 'width:32px;height:32px;border-radius:8px;border:1px solid var(--danger-soft);background:var(--danger-soft);color:var(--danger);cursor:pointer;font-size:13px;margin-left:5px';
$editId = (int) ($_GET['edit'] ?? 0);
?>
<div style="animation:fade .25s">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <p style="margin:0;color:var(--muted);font-size:13.5px;max-width:520px">เพิ่ม ลบ แก้ไขชื่อ และเปิด/ปิดการใช้งานประเภทงานวิจัย ประเภทที่ปิดจะไม่แสดงในฟอร์มนำเข้าและหน้าสาธารณะ</p>
    <form method="post" action="<?= h(url('admin/categories')) ?>" style="display:flex;gap:8px">
      <?= csrf_field() ?>
      <input name="name" placeholder="ชื่อประเภทใหม่…" required style="<?= $inputStyle ?>;width:220px;box-shadow:var(--shadow)"/>
      <button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:0 18px;font-weight:600;font-size:13.5px;cursor:pointer;box-shadow:var(--shadow);white-space:nowrap">+ เพิ่มประเภท</button>
    </form>
  </div>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden">
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead><tr style="background:var(--surface-2);text-align:left;color:var(--muted)">
        <th style="padding:13px 20px;font-weight:600">ประเภทงานวิจัย</th>
        <th style="padding:13px 12px;font-weight:600;text-align:center">จำนวนงานวิจัย</th>
        <th style="padding:13px 12px;font-weight:600;text-align:center">สถานะ</th>
        <th style="padding:13px 20px;font-weight:600;text-align:right">จัดการ</th>
      </tr></thead>
      <tbody>
        <?php foreach ($cats as $c): ?>
          <tr style="border-top:1px solid var(--border-2)">
            <td style="padding:14px 20px">
              <div style="display:flex;align-items:center;gap:11px">
                <span style="width:11px;height:11px;border-radius:3px;background:<?= h($c['color']) ?>;flex:none"></span>
                <?php if ($editId === $c['id']): ?>
                  <form method="post" action="<?= h(url('admin/categories/' . $c['id'] . '/rename')) ?>" style="display:flex;gap:6px;align-items:center">
                    <?= csrf_field() ?>
                    <input name="name" value="<?= h($c['name']) ?>" autofocus style="<?= $inputStyle ?>;padding:7px 10px;width:280px"/>
                    <button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:8px;padding:7px 12px;font-weight:600;font-size:12.5px;cursor:pointer">บันทึก</button>
                    <a href="<?= h(url('admin/categories')) ?>" style="color:var(--muted);font-size:12.5px;text-decoration:none">ยกเลิก</a>
                  </form>
                <?php else: ?>
                  <span style="font-weight:500"><?= h($c['name']) ?></span>
                <?php endif; ?>
              </div>
            </td>
            <td style="padding:14px 12px;text-align:center;color:var(--muted);font-weight:600"><?= (int) $c['count'] ?></td>
            <td style="padding:14px 12px;text-align:center">
              <form method="post" action="<?= h(url('admin/categories/' . $c['id'] . '/toggle')) ?>" style="margin:0">
                <?= csrf_field() ?>
                <button type="submit" title="เปิด/ปิด" style="width:44px;height:25px;border-radius:99px;border:none;cursor:pointer;position:relative;transition:background .18s;background:<?= $c['enabled'] ? 'var(--primary)' : 'var(--surface-3)' ?>">
                  <span style="position:absolute;top:3px;left:<?= $c['enabled'] ? '22px' : '3px' ?>;width:19px;height:19px;border-radius:50%;background:#fff;transition:left .18s;box-shadow:0 1px 2px rgba(0,0,0,.3)"></span>
                </button>
                <div style="font-size:11px;color:<?= $c['enabled'] ? 'var(--ok)' : 'var(--faint)' ?>;font-weight:600;margin-top:3px"><?= $c['enabled'] ? 'เปิดใช้งาน' : 'ปิด' ?></div>
              </form>
            </td>
            <td style="padding:14px 20px;text-align:right;white-space:nowrap">
              <a href="<?= h(url('admin/categories?edit=' . $c['id'])) ?>" title="แก้ไขชื่อ" style="<?= $iconBtn ?>;display:inline-grid;place-items:center;text-decoration:none;vertical-align:middle">✎</a>
              <form method="post" action="<?= h(url('admin/categories/' . $c['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('ลบประเภทนี้? งานวิจัยเดิมจะไม่ถูกลบแต่จะไม่มีประเภท')"><?= csrf_field() ?><button type="submit" title="ลบ" style="<?= $iconBtnDanger ?>">🗑</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
