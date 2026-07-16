<?php
declare(strict_types=1);

/**
 * Idempotent migration for databases created before self-registration existed.
 *
 * - adds users.status (pending | approved | suspended), backfilled from the old
 *   `active` column, then drops `active`
 * - creates the settings table and seeds require_approval = '1'
 *
 * Safe to run multiple times. Run:  php migrate.php   (or open in a browser)
 * Fresh installs via install.php already include these — this is only for upgrades.
 */

require __DIR__ . '/app/App.php';
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/helpers.php';

App::boot();

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}
$log = static function (string $m): void { echo $m . "\n"; };

try {
    $pdo    = Database::pdo();
    $dbName = App::config('db')['name'];

    $hasColumn = static function (PDO $pdo, string $db, string $table, string $col): bool {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?'
        );
        $stmt->execute([$db, $table, $col]);
        return (int) $stmt->fetchColumn() > 0;
    };

    // 1. users.status
    if (!$hasColumn($pdo, $dbName, 'users', 'status')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'approved' AFTER dept");
        $log('✓ เพิ่มคอลัมน์ users.status');
    } else {
        $log('• users.status มีอยู่แล้ว');
    }

    // 2. backfill from active, then drop it
    if ($hasColumn($pdo, $dbName, 'users', 'active')) {
        $pdo->exec("UPDATE users SET status = CASE WHEN active = 1 THEN 'approved' ELSE 'suspended' END");
        $pdo->exec('ALTER TABLE users DROP COLUMN active');
        $log('✓ ย้ายข้อมูลจาก active → status และลบคอลัมน์ active');
    } else {
        $log('• ไม่มีคอลัมน์ active (ข้ามการย้ายข้อมูล)');
    }

    // 3. settings table
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            setting_key    VARCHAR(64)  NOT NULL PRIMARY KEY,
            setting_value  TEXT         DEFAULT NULL,
            updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    $pdo->prepare('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)')
        ->execute(['require_approval', '1']);
    $log('✓ สร้างตาราง settings และตั้งค่า require_approval');

    $log('');
    $log('=== ปรับปรุงฐานข้อมูลเรียบร้อย ===');
} catch (Throwable $e) {
    http_response_code(500);
    $log('✗ ปรับปรุงไม่สำเร็จ: ' . $e->getMessage());
}
