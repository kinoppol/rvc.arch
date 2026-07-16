<?php
$user = Auth::user() ?? ['name' => '', 'email' => '', 'role' => ''];
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:11px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$labelStyle = 'display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin-bottom:6px';
?>
<div style="animation:fade .25s;max-width:520px">
  <!-- account summary -->
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px 22px;box-shadow:var(--shadow);margin-bottom:18px;display:flex;align-items:center;gap:14px">
    <span style="width:48px;height:48px;border-radius:50%;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:600;font-size:16px;flex:none"><?= h(name_initial($user['name'])) ?></span>
    <div>
      <div style="font-weight:600;font-size:15px"><?= h($user['name']) ?></div>
      <div style="font-size:12.5px;color:var(--muted)"><?= h($user['email']) ?> · <?= h($user['role']) ?></div>
    </div>
  </div>

  <!-- change password -->
  <form method="post" action="<?= h(url('admin/account/password')) ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow)">
    <?= csrf_field() ?>
    <div style="font-weight:700;font-size:15px;margin-bottom:16px">เปลี่ยนรหัสผ่าน</div>

    <label style="<?= $labelStyle ?>">รหัสผ่านปัจจุบัน</label>
    <input type="password" name="current" required autocomplete="current-password" style="<?= $inputStyle ?>;margin-bottom:14px"/>

    <label style="<?= $labelStyle ?>">รหัสผ่านใหม่ (อย่างน้อย 6 ตัวอักษร)</label>
    <input type="password" name="new" required minlength="6" autocomplete="new-password" style="<?= $inputStyle ?>;margin-bottom:14px"/>

    <label style="<?= $labelStyle ?>">ยืนยันรหัสผ่านใหม่</label>
    <input type="password" name="confirm" required minlength="6" autocomplete="new-password" style="<?= $inputStyle ?>;margin-bottom:22px"/>

    <button type="submit" style="background:var(--primary);color:#fff;border:none;border-radius:10px;padding:12px 22px;font-weight:600;font-size:14px;cursor:pointer">บันทึกรหัสผ่านใหม่</button>
  </form>
</div>
