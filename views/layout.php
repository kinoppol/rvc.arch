<?php
/** @var string $content */
$section    = $section    ?? 'public';
$publicView = $publicView ?? 'home';
$adminView  = $adminView  ?? 'dashboard';
$memberView = $memberView ?? 'home';
$title      = $title      ?? '';
$bare       = $bare       ?? false;
$app        = App::config('app');
$flashes    = take_flash();
?><!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title ? $title . ' · ' : '') . h($app['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= h(asset('app.css')) ?>">
<script src="<?= h(asset('theme.js')) ?>"></script>
</head>
<body>
<div class="app-root" data-theme="light" style="display:flex;flex-direction:column">
<?php if ($bare): ?>
    <?= $content ?>
<?php elseif ($section === 'admin'): ?>
    <?= App::partial('partials/admin_shell', [
        'content'   => $content,
        'adminView' => $adminView,
        'title'     => $title,
    ]) ?>
<?php elseif ($section === 'member'): ?>
    <?= App::partial('partials/member_shell', [
        'content'    => $content,
        'memberView' => $memberView,
        'title'      => $title,
    ]) ?>
<?php else: ?>
    <?= App::partial('partials/public_header', ['publicView' => $publicView]) ?>
    <?= $content ?>
    <?= App::partial('partials/public_footer') ?>
<?php endif; ?>

<?php foreach ($flashes as $f): ?>
    <div data-toast style="position:fixed;bottom:26px;left:50%;transform:translateX(-50%);z-index:99;background:var(--text);color:var(--bg);padding:13px 22px;border-radius:11px;font-size:13.5px;font-weight:500;box-shadow:var(--shadow-lg);animation:toastIn .25s;display:flex;align-items:center;gap:10px">
        <span style="width:20px;height:20px;border-radius:50%;background:<?= $f['type'] === 'error' ? 'var(--danger)' : 'var(--ok)' ?>;color:#fff;display:grid;place-items:center;font-size:12px;flex:none"><?= $f['type'] === 'error' ? '!' : '✓' ?></span>
        <?= h($f['message']) ?>
    </div>
<?php endforeach; ?>

<!-- Logout confirm modal -->
<div id="logoutModal" style="display:none;position:fixed;inset:0;z-index:200;align-items:center;justify-content:center">
  <div id="logoutOverlay" style="position:absolute;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);animation:fadeIn .2s"></div>
  <div style="position:relative;z-index:1;background:var(--surface);border:1px solid var(--border);border-radius:20px;box-shadow:0 24px 60px rgba(0,0,0,.2);padding:36px 32px 28px;max-width:380px;width:calc(100% - 48px);animation:slideUp .22s;text-align:center">
    <!-- icon -->
    <div style="width:64px;height:64px;border-radius:50%;background:var(--danger-soft,#fee2e2);color:var(--danger,#ef4444);display:grid;place-items:center;margin:0 auto 18px">
      <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
        <polyline points="16 17 21 12 16 7"/>
        <line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </div>
    <div style="font-size:19px;font-weight:700;margin-bottom:8px">ออกจากระบบ?</div>
    <div style="font-size:14px;color:var(--muted);line-height:1.6;margin-bottom:26px">คุณต้องการออกจากระบบใช่หรือไม่<br>จะต้องเข้าสู่ระบบใหม่เพื่อใช้งานต่อ</div>
    <div style="display:flex;gap:10px">
      <button id="logoutCancel" type="button" style="flex:1;height:44px;border-radius:11px;border:1px solid var(--border);background:var(--surface-2);color:var(--text);font-size:14.5px;font-weight:600;cursor:pointer;transition:background .15s" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='var(--surface-2)'">ยกเลิก</button>
      <button id="logoutConfirm" type="button" style="flex:1;height:44px;border-radius:11px;border:none;background:var(--danger,#ef4444);color:#fff;font-size:14.5px;font-weight:700;cursor:pointer;transition:opacity .15s;display:flex;align-items:center;justify-content:center;gap:7px" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        ออกจากระบบ
      </button>
    </div>
  </div>
</div>
<style>
@keyframes fadeIn  { from { opacity:0 } to { opacity:1 } }
@keyframes slideUp { from { opacity:0; transform:translateY(16px) scale(.97) } to { opacity:1; transform:none } }
</style>
<script>
(function () {
  var modal    = document.getElementById('logoutModal');
  var overlay  = document.getElementById('logoutOverlay');
  var btnCancel  = document.getElementById('logoutCancel');
  var btnConfirm = document.getElementById('logoutConfirm');
  var pendingForm = null;

  function openModal(form) {
    pendingForm = form;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    btnCancel.focus();
  }
  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
    pendingForm = null;
  }

  /* intercept ALL logout form submits */
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form.action && form.action.indexOf('/logout') !== -1) {
      e.preventDefault();
      openModal(form);
    }
  });

  btnCancel.addEventListener('click', closeModal);
  overlay.addEventListener('click', closeModal);
  btnConfirm.addEventListener('click', function () {
    if (pendingForm) {
      closeModal();
      pendingForm.submit();
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    if (e.key === 'Enter'  && modal.style.display === 'flex') btnConfirm.click();
  });
})();
</script>

<script src="<?= h(asset('admin.js')) ?>"></script>
</div>
</body>
</html>
