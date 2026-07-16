<?php
/** @var array $old @var array $roles @var array $depts */
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:12px 13px;font-size:14.5px;background:var(--surface-2);color:var(--text);outline:none';
$labelStyle = 'display:block;font-size:12.5px;font-weight:500;color:var(--muted);margin-bottom:6px';
?>
<div style="min-height:100vh;display:grid;place-items:center;padding:40px 24px;background:linear-gradient(160deg, color-mix(in srgb, var(--primary) 12%, var(--bg)), var(--bg))">
  <div style="width:100%;max-width:440px">
    <div style="text-align:center;margin-bottom:22px">
      <div style="width:52px;height:52px;border-radius:13px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;font-size:22px;margin:0 auto 14px;box-shadow:var(--shadow-lg)">RV</div>
      <div style="font-weight:700;font-size:19px">สมัครสมาชิก</div>
      <div style="font-size:13px;color:var(--muted);margin-top:3px">สำหรับครูและนักศึกษา วิทยาลัยอาชีวศึกษาร้อยเอ็ด</div>
    </div>
    <form method="post" action="<?= h(url('register')) ?>" style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:26px;box-shadow:var(--shadow-lg)">
      <?= csrf_field() ?>
      <label style="<?= $labelStyle ?>">ชื่อ-สกุล</label>
      <input name="name" value="<?= h($old['name']) ?>" required autofocus placeholder="เช่น นางสาวสมหญิง ใจดี" style="<?= $inputStyle ?>;margin-bottom:14px"/>

      <label style="<?= $labelStyle ?>">อีเมล (ใช้เข้าสู่ระบบ)</label>
      <input type="email" name="email" value="<?= h($old['email']) ?>" required placeholder="you@rvc.ac.th" style="<?= $inputStyle ?>;margin-bottom:14px"/>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
        <div>
          <label style="<?= $labelStyle ?>">สถานะผู้ใช้</label>
          <select name="role" style="<?= $inputStyle ?>">
            <?php foreach ($roles as $r): ?>
              <option value="<?= h($r) ?>" <?= $old['role'] === $r ? 'selected' : '' ?>><?= h($r) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="<?= $labelStyle ?>">สาขา/แผนกวิชา</label>
          <select name="dept" style="<?= $inputStyle ?>">
            <option value="">— เลือก —</option>
            <?php foreach ($depts as $d): ?>
              <option value="<?= h($d) ?>" <?= $old['dept'] === $d ? 'selected' : '' ?>><?= h($d) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:22px">
        <div>
          <label style="<?= $labelStyle ?>">รหัสผ่าน (≥ 6 ตัว)</label>
          <input type="password" name="password" required placeholder="••••••••" style="<?= $inputStyle ?>"/>
        </div>
        <div>
          <label style="<?= $labelStyle ?>">ยืนยันรหัสผ่าน</label>
          <input type="password" name="password2" required placeholder="••••••••" style="<?= $inputStyle ?>"/>
        </div>
      </div>

      <button type="submit" style="width:100%;background:var(--primary);color:#fff;border:none;border-radius:11px;padding:13px;font-weight:600;font-size:15px;cursor:pointer">สมัครสมาชิก</button>
    </form>
    <div style="text-align:center;margin-top:18px;font-size:13px;color:var(--muted)">
      มีบัญชีอยู่แล้ว? <a href="<?= h(url('login')) ?>" style="color:var(--primary-text);font-weight:600;text-decoration:none">เข้าสู่ระบบ</a>
      <span style="margin:0 8px;color:var(--faint)">·</span>
      <a href="<?= h(url('')) ?>" style="color:var(--muted);text-decoration:none">กลับหน้าแรก</a>
    </div>
  </div>
</div>
