<?php
/** @var array $users @var array $depts @var array $roles @var bool $requireApproval @var int $pendingCount */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$iconBtn = 'width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--muted);cursor:pointer;font-size:13px;margin-left:5px';
$showAdd = isset($_GET['add']);
$me = (int) (Auth::user()['id'] ?? 0);
?>
<div style="animation:fade .25s">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <p style="margin:0;color:var(--muted);font-size:13.5px">จัดการบัญชีผู้ใช้ สิทธิ์การเข้าถึง และการอนุมัติสมาชิกที่สมัครเข้ามา</p>
    <a href="<?= h(url('admin/users' . ($showAdd ? '' : '?add=1'))) ?>" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:11px 18px;font-weight:600;font-size:13.5px;cursor:pointer;box-shadow:var(--shadow);text-decoration:none"><?= $showAdd ? 'ปิดฟอร์ม' : '+ เพิ่มผู้ใช้' ?></a>
  </div>

  <!-- registration approval setting -->
  <form method="post" action="<?= h(url('admin/users/settings')) ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:18px 20px;box-shadow:var(--shadow);margin-bottom:16px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <?= csrf_field() ?>
    <div style="flex:1;min-width:240px">
      <div style="font-weight:600;font-size:14.5px">การสมัครสมาชิกเอง</div>
      <div style="font-size:12.5px;color:var(--muted);margin-top:2px">เมื่อเปิด ผู้ที่สมัครใหม่จะมีสถานะ “รอการอนุมัติ” และเข้าใช้งานไม่ได้จนกว่าผู้ดูแลจะอนุมัติ</div>
    </div>
    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13.5px;font-weight:600;white-space:nowrap">
      <input type="checkbox" name="require_approval" value="1" onchange="this.form.submit()" <?= $requireApproval ? 'checked' : '' ?> style="width:18px;height:18px;cursor:pointer"/>
      ต้องอนุมัติสมาชิกใหม่ก่อนใช้งาน
    </label>
    <noscript><button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:9px;padding:9px 14px;font-weight:600;font-size:13px;cursor:pointer">บันทึก</button></noscript>
  </form>

  <?php if ($pendingCount > 0): ?>
    <div style="display:flex;align-items:center;gap:10px;background:var(--warn-soft);color:var(--warn);border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13.5px;font-weight:600">
      <span style="width:22px;height:22px;border-radius:50%;background:var(--warn);color:#fff;display:grid;place-items:center;font-size:12px;flex:none">!</span>
      มีสมาชิกใหม่ <?= (int) $pendingCount ?> รายการรอการอนุมัติ (แสดงอยู่บนสุดของตาราง)
    </div>
  <?php endif; ?>

  <?php if ($showAdd): ?>
    <form method="post" action="<?= h(url('admin/users')) ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow);margin-bottom:16px">
      <?= csrf_field() ?>
      <div style="font-weight:600;font-size:15px;margin-bottom:14px">เพิ่มผู้ใช้งานใหม่ (อนุมัติทันที)</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
        <div><label style="font-size:12.5px;color:var(--muted);display:block;margin-bottom:5px">ชื่อ-สกุล</label><input name="name" required style="<?= $inputStyle ?>"/></div>
        <div><label style="font-size:12.5px;color:var(--muted);display:block;margin-bottom:5px">อีเมล</label><input type="email" name="email" required style="<?= $inputStyle ?>"/></div>
        <div><label style="font-size:12.5px;color:var(--muted);display:block;margin-bottom:5px">รหัสผ่าน (≥ 6 ตัว)</label><input type="password" name="password" required style="<?= $inputStyle ?>"/></div>
        <div><label style="font-size:12.5px;color:var(--muted);display:block;margin-bottom:5px">บทบาท</label><select name="role" style="<?= $inputStyle ?>"><?php foreach ($roles as $r): ?><option><?= h($r) ?></option><?php endforeach; ?></select></div>
        <div><label style="font-size:12.5px;color:var(--muted);display:block;margin-bottom:5px">สาขา/แผนก</label><select name="dept" style="<?= $inputStyle ?>"><option value="">—</option><?php foreach ($depts as $d): ?><option><?= h($d) ?></option><?php endforeach; ?></select></div>
      </div>
      <div style="margin-top:14px"><button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:11px 20px;font-weight:600;font-size:13.5px;cursor:pointer">บันทึกผู้ใช้</button></div>
    </form>
  <?php endif; ?>

  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden">
    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse;font-size:13.5px;min-width:640px">
        <thead><tr style="background:var(--surface-2);text-align:left;color:var(--muted)">
          <th style="padding:13px 20px;font-weight:600">ผู้ใช้</th>
          <th style="padding:13px 12px;font-weight:600">บทบาท</th>
          <th style="padding:13px 12px;font-weight:600">สาขา/แผนก</th>
          <th style="padding:13px 12px;font-weight:600;text-align:center">งานวิจัย</th>
          <th style="padding:13px 12px;font-weight:600;text-align:center">สถานะ</th>
          <th style="padding:13px 20px;font-weight:600;text-align:right">จัดการ</th>
        </tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr style="border-top:1px solid var(--border-2);<?= $u['status'] === 'suspended' ? 'opacity:.6' : '' ?><?= $u['status'] === 'pending' ? 'background:color-mix(in srgb, var(--warn) 7%, transparent)' : '' ?>">
              <td style="padding:13px 20px"><div style="display:flex;align-items:center;gap:11px"><span style="width:34px;height:34px;border-radius:50%;background:var(--primary-soft);color:var(--primary-text);display:grid;place-items:center;font-weight:600;font-size:12px;flex:none"><?= h($u['initial']) ?></span><div><div style="font-weight:500"><?= h($u['name']) ?></div><div style="font-size:11.5px;color:var(--muted)"><?= h($u['email']) ?></div></div></div></td>
              <td style="padding:13px 12px"><span style="font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:999px;background:<?= h($u['roleBg']) ?>;color:<?= h($u['roleFg']) ?>"><?= h($u['role']) ?></span></td>
              <td style="padding:13px 12px;color:var(--muted)"><?= h($u['dept'] ?: '—') ?></td>
              <td style="padding:13px 12px;text-align:center;font-weight:600"><?= (int) $u['count'] ?></td>
              <td style="padding:13px 12px;text-align:center"><span style="font-size:11.5px;font-weight:600;padding:4px 10px;border-radius:999px;background:<?= h($u['statusBg']) ?>;color:<?= h($u['statusFg']) ?>"><?= h($u['statusLabel']) ?></span></td>
              <td style="padding:13px 20px;text-align:right;white-space:nowrap">
                <?php if ($u['id'] === $me): ?>
                  <span style="font-size:11.5px;color:var(--faint)">บัญชีของคุณ</span>
                <?php elseif ($u['status'] === 'pending'): ?>
                  <form method="post" action="<?= h(url('admin/users/' . $u['id'] . '/approve')) ?>" style="display:inline"><?= csrf_field() ?><button type="submit" style="background:var(--ok);color:#fff;border:none;border-radius:8px;padding:7px 14px;font-weight:600;font-size:12.5px;cursor:pointer">อนุมัติ</button></form>
                  <form method="post" action="<?= h(url('admin/users/' . $u['id'] . '/suspend')) ?>" style="display:inline" onsubmit="return confirm('ปฏิเสธ/ระงับผู้สมัครรายนี้?')"><?= csrf_field() ?><button type="submit" title="ปฏิเสธ" style="<?= $iconBtn ?>">⊘</button></form>
                <?php elseif ($u['status'] === 'approved'): ?>
                  <form method="post" action="<?= h(url('admin/users/' . $u['id'] . '/suspend')) ?>" style="display:inline"><?= csrf_field() ?><button type="submit" title="ระงับการใช้งาน" style="<?= $iconBtn ?>">⊘</button></form>
                <?php else: /* suspended */ ?>
                  <form method="post" action="<?= h(url('admin/users/' . $u['id'] . '/approve')) ?>" style="display:inline"><?= csrf_field() ?><button type="submit" title="เปิดใช้งานอีกครั้ง" style="<?= $iconBtn ?>">✓</button></form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
