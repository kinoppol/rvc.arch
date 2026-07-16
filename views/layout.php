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

<script src="<?= h(asset('admin.js')) ?>"></script>
</div>
</body>
</html>
