<?php
/** @var string $email */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:12px 13px;font-size:14.5px;background:var(--surface-2);color:var(--text);outline:none';
?>
<div style="min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(160deg, color-mix(in srgb, var(--primary) 12%, var(--bg)), var(--bg))">
  <div style="width:100%;max-width:400px">
    <div style="text-align:center;margin-bottom:22px">
      <div style="width:52px;height:52px;border-radius:13px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;font-size:22px;margin:0 auto 14px;box-shadow:var(--shadow-lg)">RV</div>
      <div style="font-weight:700;font-size:19px">เข้าสู่ระบบผู้ดูแล</div>
      <div style="font-size:13px;color:var(--muted);margin-top:3px">ระบบคลังงานวิจัย · วิทยาลัยอาชีวศึกษาร้อยเอ็ด</div>
    </div>
    <form method="post" action="<?= h(url('login')) ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:26px;box-shadow:var(--shadow-lg)">
      <?= csrf_field() ?>
      <label style="display:block;font-size:12.5px;font-weight:500;color:var(--muted);margin-bottom:6px">อีเมล</label>
      <input type="email" name="email" value="<?= h($email) ?>" required autofocus placeholder="you@rvc.ac.th" style="<?= $inputStyle ?>;margin-bottom:16px"/>
      <label style="display:block;font-size:12.5px;font-weight:500;color:var(--muted);margin-bottom:6px">รหัสผ่าน</label>
      <input type="password" name="password" required placeholder="••••••••" style="<?= $inputStyle ?>;margin-bottom:22px"/>
      <button type="submit" style="width:100%;background:var(--primary);color:#fff;border:none;border-radius:11px;padding:13px;font-weight:600;font-size:15px;cursor:pointer">เข้าสู่ระบบ</button>
    </form>
    <div style="text-align:center;margin-top:18px;font-size:13px;color:var(--muted)">
      ยังไม่มีบัญชี? <a href="<?= h(url('register')) ?>" style="color:var(--primary-text);font-weight:600;text-decoration:none">สมัครสมาชิก</a>
      <span style="margin:0 8px;color:var(--faint)">·</span>
      <a href="<?= h(url('')) ?>" style="color:var(--muted);text-decoration:none">กลับสู่เว็บสาธารณะ</a>
    </div>
  </div>
</div>
