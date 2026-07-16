<?php
declare(strict_types=1);

/**
 * Web-based installer for the Research Repository app.
 *
 * Open in a browser (http://your-host/rvc.arch/install.php) to fill in the
 * database connection, admin account and options through a form. It tests the
 * connection, creates the database, loads sql/schema.sql (DESTRUCTIVE — drops
 * existing tables), writes config/config.php, creates the admin user and can
 * seed sample data.
 *
 * CLI mode (`php install.php`) still works for local/dev setup and uses the
 * existing config/config.php with the documented demo admin.
 *
 * SECURITY: delete or restrict this file after a successful install.
 */

require __DIR__ . '/app/App.php'; // for App::CHAPTER_NAMES (no App::boot() — we own config here)

$root       = __DIR__;
$configPath = $root . '/config/config.php';
$lockPath   = $root . '/config/installed.lock';

/* ==========================================================
 *  CLI mode — preserve simple dev behaviour
 * ========================================================== */
if (PHP_SAPI === 'cli') {
    if (!is_file($configPath)) {
        fwrite(STDERR, "config/config.php not found. Open install.php in a browser to run the setup form.\n");
        exit(1);
    }
    $cfg = require $configPath;
    try {
        $summary = run_install([
            'root'        => $root,
            'db'          => $cfg['db'],
            'app'         => $cfg['app'] ?? default_app(),
            'admin'       => ['name' => 'นายสมชาย ใจดี', 'email' => 'somchai@rvc.ac.th', 'password' => 'admin1234'],
            'seedSample'  => true,
            'writeConfig' => false, // keep the existing config file untouched
        ], static function (string $m): void { echo $m . "\n"; });
        echo "\n====================================================\n";
        echo " ติดตั้งเสร็จสมบูรณ์\n";
        echo " เข้าสู่ระบบผู้ดูแล: somchai@rvc.ac.th / admin1234\n";
        echo "====================================================\n";
    } catch (Throwable $e) {
        fwrite(STDERR, '✗ ติดตั้งไม่สำเร็จ: ' . $e->getMessage() . "\n");
        exit(1);
    }
    return;
}

/* ==========================================================
 *  Web mode
 * ========================================================== */
header('Content-Type: text/html; charset=utf-8');

$existing = is_file($configPath) ? (require $configPath) : null;
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

$form = [
    'db_host'     => $existing['db']['host'] ?? '127.0.0.1',
    'db_port'     => (string) ($existing['db']['port'] ?? 3306),
    'db_name'     => $existing['db']['name'] ?? 'rvc_arch',
    'db_user'     => $existing['db']['user'] ?? 'root',
    'db_pass'     => '',
    'app_name'    => $existing['app']['name'] ?? 'ระบบคลังงานวิจัย',
    'app_org'     => $existing['app']['org'] ?? 'วิทยาลัยอาชีวศึกษาร้อยเอ็ด',
    'admin_name'  => 'ผู้ดูแลระบบ',
    'admin_email' => 'admin@rvc.ac.th',
    'seed'        => true,
];

$errors  = [];
$log     = [];
$summary = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['db_host', 'db_port', 'db_name', 'db_user', 'db_pass', 'app_name', 'app_org', 'admin_name', 'admin_email'] as $k) {
        $form[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $form['seed']  = isset($_POST['seed']);
    $adminPass     = (string) ($_POST['admin_pass'] ?? '');
    $adminPass2    = (string) ($_POST['admin_pass2'] ?? '');
    $confirm       = isset($_POST['confirm']);

    // ---- validation ----
    if ($form['db_host'] === '')                                    $errors[] = 'กรุณาระบุโฮสต์ของฐานข้อมูล';
    if (!ctype_digit($form['db_port']) || (int) $form['db_port'] < 1 || (int) $form['db_port'] > 65535)
                                                                    $errors[] = 'พอร์ตต้องเป็นตัวเลข 1–65535';
    if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $form['db_name']))    $errors[] = 'ชื่อฐานข้อมูลใช้ได้เฉพาะ a–z, 0–9 และ _ (ไม่เกิน 64 ตัว)';
    if ($form['db_user'] === '')                                   $errors[] = 'กรุณาระบุชื่อผู้ใช้ฐานข้อมูล';
    if ($form['admin_name'] === '')                                $errors[] = 'กรุณาระบุชื่อผู้ดูแลระบบ';
    if (!filter_var($form['admin_email'], FILTER_VALIDATE_EMAIL))  $errors[] = 'อีเมลผู้ดูแลไม่ถูกต้อง';
    if (strlen($adminPass) < 8)                                    $errors[] = 'รหัสผ่านผู้ดูแลต้องมีอย่างน้อย 8 ตัวอักษร';
    if ($adminPass !== $adminPass2)                                $errors[] = 'รหัสผ่านผู้ดูแลและการยืนยันไม่ตรงกัน';
    if ($form['app_name'] === '')                                  $errors[] = 'กรุณาระบุชื่อระบบ';
    if (!$confirm)                                                 $errors[] = 'กรุณายืนยันว่าเข้าใจว่าการติดตั้งจะลบตารางเดิมทั้งหมดในฐานข้อมูลนี้';

    // ---- run ----
    if (!$errors) {
        try {
            $summary = run_install([
                'root'  => $root,
                'db'    => [
                    'host' => $form['db_host'], 'port' => (int) $form['db_port'], 'name' => $form['db_name'],
                    'user' => $form['db_user'], 'pass' => $form['db_pass'], 'charset' => 'utf8mb4',
                ],
                'app'   => ['name' => $form['app_name'], 'org' => $form['app_org']],
                'admin' => ['name' => $form['admin_name'], 'email' => $form['admin_email'], 'password' => $adminPass],
                'seedSample'  => $form['seed'],
                'writeConfig' => true,
            ], static function (string $m) use (&$log): void { $log[] = $m; });
        } catch (Throwable $e) {
            $errors[] = 'ติดตั้งไม่สำเร็จ: ' . $e->getMessage();
        }
    }
}

$locked = is_file($lockPath) && !$summary;

/* ---------- tiny view helpers ---------- */
function e(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
$inputStyle = 'width:100%;border:1px solid var(--border);border-radius:9px;padding:10px 12px;font-size:14px;background:var(--surface-2);color:var(--text);outline:none';
$labelStyle = 'display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin-bottom:6px';
$cardStyle  = 'background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow);margin-bottom:18px';
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ติดตั้งระบบ · Research Repository</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e($base) ?>/assets/app.css">
</head>
<body>
<div class="app-root" data-theme="light" style="min-height:100vh;padding:40px 20px">
  <div style="max-width:760px;margin:0 auto">
    <div style="text-align:center;margin-bottom:24px">
      <div style="width:52px;height:52px;border-radius:13px;background:var(--primary);color:#fff;display:grid;place-items:center;font-weight:700;font-size:22px;margin:0 auto 14px;box-shadow:var(--shadow-lg)">RV</div>
      <h1 style="font-size:22px;font-weight:700;margin:0">ติดตั้งระบบคลังงานวิจัย</h1>
      <p style="color:var(--muted);font-size:13.5px;margin:6px 0 0">ตั้งค่าการเชื่อมต่อฐานข้อมูลและบัญชีผู้ดูแล เพื่อติดตั้งบนเซิร์ฟเวอร์</p>
    </div>

<?php if ($summary): ?>
    <!-- ===== success ===== -->
    <div style="<?= $cardStyle ?>;border-color:var(--ok)">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
        <span style="width:34px;height:34px;border-radius:50%;background:var(--ok);color:#fff;display:grid;place-items:center;font-size:17px;flex:none">✓</span>
        <div style="font-weight:700;font-size:17px">ติดตั้งเสร็จสมบูรณ์</div>
      </div>
      <pre style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:14px;font-size:12.5px;line-height:1.7;overflow:auto;white-space:pre-wrap;margin:0 0 16px"><?php foreach ($log as $l) echo e($l) . "\n"; ?></pre>
      <dl style="display:grid;grid-template-columns:auto 1fr;gap:8px 16px;font-size:13.5px;margin:0 0 18px">
        <dt style="color:var(--muted)">ประเภทงานวิจัย</dt><dd style="margin:0;font-weight:600"><?= (int) $summary['categories'] ?> รายการ</dd>
        <dt style="color:var(--muted)">ผู้ใช้งาน</dt><dd style="margin:0;font-weight:600"><?= (int) $summary['users'] ?> บัญชี</dd>
        <dt style="color:var(--muted)">งานวิจัย</dt><dd style="margin:0;font-weight:600"><?= (int) $summary['research'] ?> รายการ</dd>
        <dt style="color:var(--muted)">อีเมลผู้ดูแล</dt><dd style="margin:0;font-weight:600"><?= e($summary['admin_email']) ?></dd>
      </dl>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= e($base) ?>/" style="background:var(--primary);color:#fff;text-decoration:none;border-radius:10px;padding:11px 20px;font-weight:600;font-size:14px">ไปที่หน้าเว็บ</a>
        <a href="<?= e($base) ?>/login" style="background:var(--surface-2);color:var(--text);border:1px solid var(--border);text-decoration:none;border-radius:10px;padding:11px 20px;font-weight:600;font-size:14px">เข้าสู่ระบบผู้ดูแล</a>
      </div>
      <div style="margin-top:16px;font-size:12.5px;color:var(--danger);background:var(--danger-soft);border-radius:10px;padding:12px 14px;line-height:1.6">
        ⚠ เพื่อความปลอดภัย <b>กรุณาลบไฟล์ <code>install.php</code></b> ออกจากเซิร์ฟเวอร์หลังติดตั้งเสร็จ
      </div>
    </div>
<?php else: ?>
    <!-- ===== form ===== -->
    <?php if ($locked): ?>
      <div style="font-size:13px;color:var(--warn);background:var(--warn-soft);border-radius:10px;padding:12px 14px;line-height:1.6;margin-bottom:18px">
        ⚠ ระบบนี้เคยถูกติดตั้งไปแล้ว การติดตั้งซ้ำจะ <b>ลบข้อมูลเดิมทั้งหมด</b> — ดำเนินการต่อเฉพาะเมื่อต้องการรีเซ็ต
      </div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div style="font-size:13px;color:var(--danger);background:var(--danger-soft);border:1px solid var(--danger);border-radius:10px;padding:14px 16px;margin-bottom:18px">
        <div style="font-weight:700;margin-bottom:6px">พบข้อผิดพลาด</div>
        <ul style="margin:0;padding-left:18px;line-height:1.7"><?php foreach ($errors as $er) echo '<li>' . e($er) . '</li>'; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= e($base) ?>/install.php">
      <div style="<?= $cardStyle ?>">
        <div style="font-weight:700;font-size:15px;margin-bottom:16px">การเชื่อมต่อฐานข้อมูล (MariaDB / MySQL)</div>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px">
          <div><label style="<?= $labelStyle ?>">โฮสต์ (Host)</label><input name="db_host" value="<?= e($form['db_host']) ?>" style="<?= $inputStyle ?>"></div>
          <div><label style="<?= $labelStyle ?>">พอร์ต (Port)</label><input name="db_port" value="<?= e($form['db_port']) ?>" style="<?= $inputStyle ?>"></div>
        </div>
        <div style="margin-bottom:14px"><label style="<?= $labelStyle ?>">ชื่อฐานข้อมูล</label><input name="db_name" value="<?= e($form['db_name']) ?>" style="<?= $inputStyle ?>"><div style="font-size:11.5px;color:var(--faint);margin-top:5px">หากยังไม่มี ระบบจะสร้างให้อัตโนมัติ</div></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div><label style="<?= $labelStyle ?>">ผู้ใช้ (User)</label><input name="db_user" value="<?= e($form['db_user']) ?>" style="<?= $inputStyle ?>"></div>
          <div><label style="<?= $labelStyle ?>">รหัสผ่าน (Password)</label><input type="password" name="db_pass" value="<?= e($form['db_pass']) ?>" placeholder="เว้นว่างได้ถ้าไม่มี" style="<?= $inputStyle ?>"></div>
        </div>
      </div>

      <div style="<?= $cardStyle ?>">
        <div style="font-weight:700;font-size:15px;margin-bottom:16px">บัญชีผู้ดูแลระบบ</div>
        <div style="margin-bottom:14px"><label style="<?= $labelStyle ?>">ชื่อ-สกุล</label><input name="admin_name" value="<?= e($form['admin_name']) ?>" style="<?= $inputStyle ?>"></div>
        <div style="margin-bottom:14px"><label style="<?= $labelStyle ?>">อีเมล (ใช้เข้าสู่ระบบ)</label><input type="email" name="admin_email" value="<?= e($form['admin_email']) ?>" style="<?= $inputStyle ?>"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div><label style="<?= $labelStyle ?>">รหัสผ่าน (≥ 8 ตัว)</label><input type="password" name="admin_pass" style="<?= $inputStyle ?>"></div>
          <div><label style="<?= $labelStyle ?>">ยืนยันรหัสผ่าน</label><input type="password" name="admin_pass2" style="<?= $inputStyle ?>"></div>
        </div>
      </div>

      <div style="<?= $cardStyle ?>">
        <div style="font-weight:700;font-size:15px;margin-bottom:16px">ข้อมูลระบบ</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div><label style="<?= $labelStyle ?>">ชื่อระบบ</label><input name="app_name" value="<?= e($form['app_name']) ?>" style="<?= $inputStyle ?>"></div>
          <div><label style="<?= $labelStyle ?>">ชื่อหน่วยงาน</label><input name="app_org" value="<?= e($form['app_org']) ?>" style="<?= $inputStyle ?>"></div>
        </div>
      </div>

      <div style="<?= $cardStyle ?>">
        <div style="font-weight:700;font-size:15px;margin-bottom:14px">ตัวเลือกการติดตั้ง</div>
        <label style="display:flex;gap:10px;align-items:flex-start;font-size:13.5px;cursor:pointer;margin-bottom:12px">
          <input type="checkbox" name="seed" value="1" <?= $form['seed'] ? 'checked' : '' ?> style="margin-top:3px">
          <span>เพิ่มข้อมูลตัวอย่าง (งานวิจัย 12 รายการ + ผู้ใช้ตัวอย่าง 4 บัญชี)<br><span style="font-size:11.5px;color:var(--faint)">เหมาะสำหรับทดลองใช้ — หากติดตั้งใช้งานจริงและต้องการเริ่มจากข้อมูลว่าง ให้ยกเลิกการเลือก</span></span>
        </label>
        <label style="display:flex;gap:10px;align-items:flex-start;font-size:13.5px;cursor:pointer;color:var(--danger)">
          <input type="checkbox" name="confirm" value="1" style="margin-top:3px">
          <span>ฉันเข้าใจว่าการติดตั้งจะ <b>ลบตารางเดิมทั้งหมด</b> ในฐานข้อมูลนี้ก่อนสร้างใหม่</span>
        </label>
      </div>

      <button type="submit" style="width:100%;background:var(--primary);color:#fff;border:none;border-radius:11px;padding:14px;font-weight:700;font-size:15px;cursor:pointer;box-shadow:var(--shadow)">เริ่มติดตั้ง</button>
    </form>
<?php endif; ?>
  </div>
</div>
</body>
</html>
<?php
/* ==========================================================
 *  Installer core
 * ========================================================== */

function default_app(): array
{
    return [
        'name' => 'ระบบคลังงานวิจัย', 'org' => 'วิทยาลัยอาชีวศึกษาร้อยเอ็ด',
        'upload_dir' => __DIR__ . '/storage/uploads', 'max_upload_bytes' => 20 * 1024 * 1024,
    ];
}

/**
 * @param array{root:string,db:array,app:array,admin:array,seedSample:bool,writeConfig:bool} $o
 * @return array{categories:int,users:int,research:int,admin_email:string}
 */
function run_install(array $o, callable $log): array
{
    $root  = $o['root'];
    $db    = $o['db'];
    $admin = $o['admin'];
    $host  = $db['host'];
    $port  = (int) $db['port'];
    $name  = $db['name'];

    if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $name)) {
        throw new RuntimeException('ชื่อฐานข้อมูลไม่ถูกต้อง');
    }

    // 1. connect to the server (no database selected yet)
    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $db['user'],
            $db['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException $e) {
        throw new RuntimeException('เชื่อมต่อฐานข้อมูลไม่ได้ — ' . $e->getMessage());
    }
    $log('✓ เชื่อมต่อ MariaDB/MySQL สำเร็จ');

    // 2. create + select database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$name}`");
    $log("✓ ฐานข้อมูล `{$name}` พร้อมใช้งาน");

    // 3. schema (drops existing tables)
    $sql = file_get_contents($root . '/sql/schema.sql');
    if ($sql === false) {
        throw new RuntimeException('อ่านไฟล์ sql/schema.sql ไม่ได้');
    }
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
    $log('✓ สร้างตารางจาก schema.sql แล้ว (ตารางเดิมถูกลบทิ้ง)');

    // 4. write config/config.php
    if ($o['writeConfig']) {
        $written = @file_put_contents($root . '/config/config.php', render_config($db, $o['app']));
        if ($written === false) {
            throw new RuntimeException('เขียนไฟล์ config/config.php ไม่ได้ — ตรวจสอบสิทธิ์การเขียนโฟลเดอร์ config/');
        }
        $log('✓ เขียนไฟล์ตั้งค่า config/config.php');
    }

    // 5. uploads dir + sample pdf
    $uploadDir = $root . '/storage/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }
    $samplePath = $uploadDir . '/sample.pdf';
    file_put_contents($samplePath, build_sample_pdf());
    $sampleSize = (int) (@filesize($samplePath) ?: 0);
    $log('✓ สร้างไฟล์ตัวอย่าง sample.pdf');

    // 6. categories (always — core taxonomy)
    $categories = [
        1 => 'งานวิจัยของครู',
        2 => 'โครงงานของนักเรียนนักศึกษา',
        3 => 'งานวิจัยสิ่งประดิษฐ์ของคนรุ่นใหม่',
        4 => 'โครงงานวิทยาศาสตร์',
    ];
    $cStmt = $pdo->prepare('INSERT INTO categories (id, name, enabled, sort_order) VALUES (?,?,1,?)');
    foreach ($categories as $id => $catName) {
        $cStmt->execute([$id, $catName, $id]);
    }
    $log('✓ เพิ่มประเภทงานวิจัย ' . count($categories) . ' รายการ');

    // 7. default settings
    $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)')
        ->execute(['require_approval', '1']); // new self-registrations wait for admin approval by default
    $log('✓ ตั้งค่าเริ่มต้น: สมาชิกที่สมัครใหม่ต้องรออนุมัติ');

    // 8. admin user (always)
    $uStmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, dept, status) VALUES (?,?,?,?,?,?)');
    $uStmt->execute([$admin['name'], $admin['email'], password_hash($admin['password'], PASSWORD_DEFAULT), 'ผู้ดูแลระบบ', 'งานศูนย์ข้อมูล', 'approved']);
    $userCount = 1;
    $log('✓ สร้างบัญชีผู้ดูแล: ' . $admin['email']);

    // 9. optional sample data
    $researchCount = 0;
    if ($o['seedSample']) {
        $sampleUsers = [
            ['นางสาวปิยะนุช ศรีวิไล', 'piyanuch@rvc.ac.th', 'ครู',             'การบัญชี'],
            ['นายอนุชิต ทองมาก',     'anuchit@rvc.ac.th',  'ครู',             'คอมพิวเตอร์ธุรกิจ'],
            ['นางสาวกมลชนก แสนสุข',  'kamon@rvc.ac.th',    'ตัวแทนนักศึกษา', 'คอมพิวเตอร์ธุรกิจ'],
            ['นายธีรภัทร โพธิ์ศรี',    'teerapat@rvc.ac.th', 'ตัวแทนนักศึกษา', 'คอมพิวเตอร์ธุรกิจ'],
        ];
        foreach ($sampleUsers as [$sn, $se, $sr, $sd]) {
            if (strcasecmp($se, $admin['email']) === 0) {
                continue; // don't collide with the admin's email
            }
            $uStmt->execute([$sn, $se, password_hash('password123', PASSWORD_DEFAULT), $sr, $sd, 'approved']);
            $userCount++;
        }

        $research = seed_research();
        $rStmt = $pdo->prepare('INSERT INTO research (category_id, dept, title_th, title_en, abstract_th, abstract_en, pub_year, academic_year, status) VALUES (?,?,?,?,?,?,?,?,?)');
        $aStmt = $pdo->prepare('INSERT INTO research_authors (research_id, name, role, sort_order) VALUES (?,?,?,?)');
        $kStmt = $pdo->prepare('INSERT INTO research_keywords (research_id, keyword, sort_order) VALUES (?,?,?)');
        $fStmt = $pdo->prepare('INSERT INTO research_files (research_id, chapter_index, chapter_name, stored_name, original_name, size_bytes, is_public, uploaded) VALUES (?,?,?,?,?,?,?,?)');

        foreach ($research as $r) {
            $rStmt->execute([$r['cat'], $r['dept'], $r['title_th'], $r['title_en'], $r['abs_th'], $r['abs_en'], $r['year'], $r['ay'], $r['status']]);
            $rid = (int) $pdo->lastInsertId();
            foreach ($r['authors'] as $i => [$an, $ar]) {
                $aStmt->execute([$rid, $an, $ar, $i]);
            }
            foreach ($r['keywords'] as $i => $kw) {
                $kStmt->execute([$rid, $kw, $i]);
            }
            foreach (App::CHAPTER_NAMES as $idx => $chName) {
                $public = $idx < 2 ? 1 : 0;
                $fStmt->execute([$rid, $idx, $chName, 'sample.pdf', $chName . '.pdf', $sampleSize, $public, 1]);
            }
            $researchCount++;
        }
        $log('✓ เพิ่มข้อมูลตัวอย่าง: ผู้ใช้ ' . ($userCount - 1) . ' บัญชี · งานวิจัย ' . $researchCount . ' รายการ');
    }

    // 10. lock file (marks a completed install)
    @file_put_contents($root . '/config/installed.lock', 'installed ' . date('c') . "\n");

    return [
        'categories'  => count($categories),
        'users'       => $userCount,
        'research'    => $researchCount,
        'admin_email' => $admin['email'],
    ];
}

/** Render config/config.php content from the entered values. */
function render_config(array $db, array $app): string
{
    $host = var_export($db['host'], true);
    $port = (int) $db['port'];
    $name = var_export($db['name'], true);
    $user = var_export($db['user'], true);
    $pass = var_export($db['pass'], true);
    $an   = var_export($app['name'], true);
    $ao   = var_export($app['org'], true);
    $when = date('Y-m-d H:i');

    return <<<PHP
<?php
/**
 * Local configuration — generated by install.php on {$when}.
 * Holds credentials; keep out of version control (see .gitignore).
 */
return [
    'db' => [
        'host'    => {$host},
        'port'    => {$port},
        'name'    => {$name},
        'user'    => {$user},
        'pass'    => {$pass},
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'             => {$an},
        'org'              => {$ao},
        'upload_dir'       => __DIR__ . '/../storage/uploads',
        'max_upload_bytes' => 20 * 1024 * 1024,
    ],
];

PHP;
}

/** Build a minimal but valid single-page PDF with correct xref offsets. */
function build_sample_pdf(): string
{
    $stream = 'BT /F1 16 Tf 24 110 Td (Research Repository) Tj 0 -26 Td (Sample chapter document) Tj ET';
    $objs = [
        1 => '<</Type/Catalog/Pages 2 0 R>>',
        2 => '<</Type/Pages/Kids[3 0 R]/Count 1>>',
        3 => '<</Type/Page/Parent 2 0 R/MediaBox[0 0 360 180]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>',
        4 => "<</Length " . strlen($stream) . ">>\nstream\n" . $stream . "\nendstream",
        5 => '<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>',
    ];
    $pdf = "%PDF-1.4\n";
    $offsets = [];
    foreach ($objs as $num => $body) {
        $offsets[$num] = strlen($pdf);
        $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $size = count($objs) + 1;
    $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
    for ($i = 1; $i < $size; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<</Size {$size}/Root 1 0 R>>\nstartxref\n{$xref}\n%%EOF";
    return $pdf;
}

/** The 12 seed research records, transcribed from the design prototype. */
function seed_research(): array
{
    $R = fn (int $cat, string $dept, int $year, int $ay, string $tth, string $ten,
             array $authors, array $keywords, string $status, string $ath, string $aen): array => [
        'cat' => $cat, 'dept' => $dept, 'year' => $year, 'ay' => $ay,
        'title_th' => $tth, 'title_en' => $ten, 'authors' => $authors, 'keywords' => $keywords,
        'status' => $status, 'abs_th' => $ath, 'abs_en' => $aen,
    ];

    return [
        $R(1, 'การบัญชี', 2568, 2567,
            'การพัฒนาระบบบัญชีอัตโนมัติสำหรับร้านค้าปลีกในชุมชน',
            'Development of an Automated Accounting System for Community Retail Shops',
            [['นางสาวปิยะนุช ศรีวิไล', 'ผู้วิจัยหลัก'], ['นายวีระพงษ์ อินทะ', 'ผู้วิจัยร่วม']],
            ['ระบบบัญชี', 'ร้านค้าปลีก', 'อัตโนมัติ', 'ชุมชน'], 'เผยแพร่',
            'งานวิจัยนี้มีวัตถุประสงค์เพื่อพัฒนาระบบบัญชีอัตโนมัติที่ช่วยลดภาระงานของผู้ประกอบการร้านค้าปลีกในชุมชน โดยออกแบบให้ใช้งานง่ายและบันทึกรายรับรายจ่ายได้แบบเรียลไทม์ ผลการทดลองใช้พบว่าลดเวลาการทำบัญชีลงร้อยละ 62 และลดข้อผิดพลาดได้อย่างมีนัยสำคัญ',
            'This research aims to develop an automated accounting system that reduces the workload of community retail operators, featuring an easy-to-use interface and real-time transaction recording.'),
        $R(2, 'คอมพิวเตอร์ธุรกิจ', 2568, 2567,
            'แอปพลิเคชันสั่งอาหารออนไลน์สำหรับร้านอาหารท้องถิ่นจังหวัดร้อยเอ็ด',
            'Online Food Ordering Application for Local Restaurants in Roi Et',
            [['นางสาวกมลชนก แสนสุข', 'ผู้วิจัยหลัก'], ['นายธีรภัทร โพธิ์ศรี', 'ผู้วิจัยร่วม'], ['นายอนุชิต ทองมาก', 'ครูที่ปรึกษา']],
            ['แอปพลิเคชัน', 'สั่งอาหารออนไลน์', 'ร้านอาหารท้องถิ่น'], 'เผยแพร่',
            'โครงงานนี้พัฒนาแอปพลิเคชันสั่งอาหารออนไลน์เพื่อช่วยร้านอาหารท้องถิ่นเพิ่มช่องทางการขาย รองรับการแจ้งเตือนออเดอร์และระบบชำระเงิน ผลการประเมินความพึงพอใจของผู้ใช้อยู่ในระดับมากที่สุด',
            'This project develops an online food ordering application to help local restaurants expand sales channels, supporting order notifications and payment integration.'),
        $R(3, 'คหกรรมศาสตร์', 2567, 2566,
            'เครื่องอบแห้งพลังงานแสงอาทิตย์สำหรับผลิตภัณฑ์ข้าวแตนสมุนไพร',
            'Solar Dryer for Herbal Rice Cracker Products',
            [['นายสิทธิชัย บุญเรือง', 'ผู้วิจัยหลัก'], ['นางสาวจิราพร คำมูล', 'ผู้วิจัยร่วม']],
            ['พลังงานแสงอาทิตย์', 'เครื่องอบแห้ง', 'ข้าวแตน', 'สิ่งประดิษฐ์'], 'เผยแพร่',
            'สิ่งประดิษฐ์นี้พัฒนาเครื่องอบแห้งพลังงานแสงอาทิตย์เพื่อทดแทนการตากแดดแบบเดิม ช่วยลดระยะเวลาการอบแห้งและควบคุมคุณภาพผลิตภัณฑ์ข้าวแตนสมุนไพรให้สม่ำเสมอ ประหยัดพลังงานและเป็นมิตรต่อสิ่งแวดล้อม',
            'This innovation develops a solar-powered dryer to replace traditional sun drying, reducing drying time and ensuring consistent quality of herbal rice crackers.'),
        $R(4, 'เทคโนโลยีสารสนเทศ', 2567, 2566,
            'การศึกษาคุณภาพน้ำในลำน้ำชีด้วยพืชกรองธรรมชาติ',
            'A Study of Water Quality in the Chi River Using Natural Filter Plants',
            [['นางสาวศศิธร วงศ์ใหญ่', 'ผู้วิจัยหลัก'], ['นายกิตติพงศ์ สุริยะ', 'ผู้วิจัยร่วม']],
            ['คุณภาพน้ำ', 'ลำน้ำชี', 'พืชกรองน้ำ', 'สิ่งแวดล้อม'], 'เผยแพร่',
            'โครงงานวิทยาศาสตร์นี้ศึกษาประสิทธิภาพของพืชกรองธรรมชาติในการปรับปรุงคุณภาพน้ำในลำน้ำชี พบว่าพืชบางชนิดสามารถลดค่าความขุ่นและปริมาณสารอินทรีย์ได้อย่างมีนัยสำคัญ',
            'This science project investigates the efficiency of natural filter plants in improving water quality in the Chi River.'),
        $R(1, 'การตลาด', 2567, 2566,
            'กลยุทธ์การตลาดออนไลน์สำหรับผลิตภัณฑ์ผ้าไหมทอมือ',
            'Online Marketing Strategy for Handwoven Silk Products',
            [['นางอรวรรณ พิมพ์ดี', 'ผู้วิจัยหลัก']],
            ['การตลาดออนไลน์', 'ผ้าไหม', 'ทอมือ', 'วิสาหกิจชุมชน'], 'เผยแพร่',
            'งานวิจัยนี้ศึกษากลยุทธ์การตลาดออนไลน์ที่เหมาะสมกับผลิตภัณฑ์ผ้าไหมทอมือของกลุ่มวิสาหกิจชุมชน โดยเน้นการเล่าเรื่องอัตลักษณ์ท้องถิ่นผ่านสื่อสังคมออนไลน์ ส่งผลให้ยอดขายเพิ่มขึ้นร้อยละ 45',
            'This research explores suitable online marketing strategies for handwoven silk products of community enterprises through local-identity storytelling.'),
        $R(2, 'การโรงแรม', 2568, 2567,
            'ระบบจองห้องพักโรงแรมด้วยคิวอาร์โค้ด',
            'Hotel Room Booking System Using QR Code',
            [['นางสาวเบญจวรรณ ทองสุข', 'ผู้วิจัยหลัก'], ['นายภาณุวัฒน์ ดวงจันทร์', 'ผู้วิจัยร่วม']],
            ['ระบบจอง', 'คิวอาร์โค้ด', 'โรงแรม'], 'รอตรวจสอบ',
            'โครงงานนี้พัฒนาระบบจองห้องพักที่ให้ลูกค้าสแกนคิวอาร์โค้ดเพื่อดูห้องว่างและจองได้ทันที ลดขั้นตอนการทำงานของพนักงานต้อนรับ',
            'This project develops a booking system allowing customers to scan a QR code to view availability and book instantly.'),
        $R(1, 'การบัญชี', 2568, 2567,
            'การพัฒนาบทเรียนออนไลน์วิชาการบัญชีเบื้องต้น',
            'Development of an Online Lesson for Introductory Accounting',
            [['นายประเสริฐ ชัยมงคล', 'ผู้วิจัยหลัก']],
            ['บทเรียนออนไลน์', 'การบัญชี', 'สื่อการสอน'], 'รอตรวจสอบ',
            'งานวิจัยนี้พัฒนาบทเรียนออนไลน์วิชาการบัญชีเบื้องต้นเพื่อส่งเสริมการเรียนรู้ด้วยตนเองของผู้เรียน ผลสัมฤทธิ์ทางการเรียนของกลุ่มทดลองสูงกว่ากลุ่มควบคุมอย่างมีนัยสำคัญ',
            'This research develops an online lesson for introductory accounting to promote self-directed learning.'),
        $R(3, 'เทคโนโลยีสารสนเทศ', 2567, 2566,
            'หุ่นยนต์รดน้ำต้นไม้อัตโนมัติควบคุมผ่านมือถือ',
            'Automatic Plant Watering Robot Controlled via Mobile',
            [['นายพงศธร แก้วมณี', 'ผู้วิจัยหลัก'], ['นางสาวสุดารัตน์ นามวงศ์', 'ผู้วิจัยร่วม']],
            ['หุ่นยนต์', 'ไอโอที', 'รดน้ำอัตโนมัติ'], 'เผยแพร่',
            'สิ่งประดิษฐ์นี้พัฒนาหุ่นยนต์รดน้ำต้นไม้ที่ควบคุมผ่านแอปพลิเคชันบนมือถือ พร้อมเซนเซอร์วัดความชื้นในดินเพื่อรดน้ำตามความต้องการจริงของพืช',
            'This innovation develops a plant-watering robot controlled via a mobile app with soil moisture sensors.'),
        $R(4, 'อาหารและโภชนาการ', 2567, 2566,
            'บรรจุภัณฑ์ย่อยสลายได้จากเปลือกข้าวโพด',
            'Biodegradable Packaging from Corn Husk',
            [['นางสาวนภัสสร ปัญญา', 'ผู้วิจัยหลัก']],
            ['บรรจุภัณฑ์', 'ย่อยสลายได้', 'เปลือกข้าวโพด', 'สิ่งแวดล้อม'], 'เผยแพร่',
            'โครงงานวิทยาศาสตร์นี้พัฒนาบรรจุภัณฑ์ย่อยสลายได้จากเปลือกข้าวโพดเหลือทิ้ง เพื่อทดแทนพลาสติกและเพิ่มมูลค่าวัสดุเหลือใช้ทางการเกษตร',
            'This science project develops biodegradable packaging from waste corn husk to replace plastic.'),
        $R(2, 'การบัญชี', 2568, 2567,
            'การจัดทำบัญชีครัวเรือนตามหลักปรัชญาเศรษฐกิจพอเพียง',
            'Household Accounting Based on the Sufficiency Economy Philosophy',
            [['นายสหรัฐ พันธ์ดี', 'ผู้วิจัยหลัก'], ['นางสาวปวีณา ศรีสุข', 'ผู้วิจัยร่วม']],
            ['บัญชีครัวเรือน', 'เศรษฐกิจพอเพียง'], 'แบบร่าง',
            'โครงงานนี้ส่งเสริมการจัดทำบัญชีครัวเรือนในชุมชนตามหลักปรัชญาเศรษฐกิจพอเพียง เพื่อสร้างวินัยทางการเงินและลดหนี้สินของครัวเรือน',
            'This project promotes household accounting in the community based on the Sufficiency Economy Philosophy.'),
        $R(2, 'คอมพิวเตอร์ธุรกิจ', 2568, 2567,
            'เว็บไซต์ประชาสัมพันธ์แหล่งท่องเที่ยวจังหวัดร้อยเอ็ด',
            'Website for Promoting Tourist Attractions in Roi Et',
            [['นางสาวชลธิชา มาลี', 'ผู้วิจัยหลัก'], ['นายรัชชานนท์ ทองคำ', 'ผู้วิจัยร่วม']],
            ['เว็บไซต์', 'การท่องเที่ยว', 'ร้อยเอ็ด'], 'ไม่เผยแพร่',
            'โครงงานนี้พัฒนาเว็บไซต์ประชาสัมพันธ์แหล่งท่องเที่ยวในจังหวัดร้อยเอ็ด รวบรวมข้อมูลสถานที่ กิจกรรม และเส้นทางการเดินทาง',
            'This project develops a website to promote tourist attractions in Roi Et province.'),
        $R(4, 'อาหารและโภชนาการ', 2567, 2566,
            'ขนมไทยประยุกต์จากแป้งข้าวหอมมะลิทุ่งกุลาร้องไห้',
            'Applied Thai Desserts from Thung Kula Rong Hai Jasmine Rice Flour',
            [['นางสาวอาทิตยา ศรีมาตย์', 'ผู้วิจัยหลัก']],
            ['ขนมไทย', 'ข้าวหอมมะลิ', 'ทุ่งกุลาร้องไห้'], 'เผยแพร่',
            'โครงงานวิทยาศาสตร์นี้ศึกษาการนำแป้งข้าวหอมมะลิทุ่งกุลาร้องไห้มาประยุกต์ทำขนมไทย เพื่อเพิ่มมูลค่าและส่งเสริมอัตลักษณ์ท้องถิ่น',
            'This science project studies the application of local jasmine rice flour to make Thai desserts.'),
    ];
}
