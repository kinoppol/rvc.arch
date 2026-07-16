<?php
declare(strict_types=1);

/**
 * Thin PDO wrapper — one shared connection per request.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = App::config('db');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo '<!doctype html><meta charset="utf-8">'
               . '<div style="font-family:sans-serif;max-width:640px;margin:60px auto;line-height:1.6">'
               . '<h1>เชื่อมต่อฐานข้อมูลไม่สำเร็จ</h1>'
               . '<p>ไม่สามารถเชื่อมต่อ MariaDB ได้ ตรวจสอบว่าได้เปิดบริการ MySQL ใน XAMPP '
               . 'และรัน <code>install.php</code> เพื่อสร้างฐานข้อมูลแล้ว</p>'
               . '<pre style="background:#f4f4f4;padding:12px;border-radius:8px;white-space:pre-wrap">'
               . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '</pre></div>';
            exit;
        }

        return self::$pdo;
    }

    /** Connect without selecting a database (used by install.php). */
    public static function serverPdo(): PDO
    {
        $cfg = App::config('db');
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $cfg['host'], $cfg['port'], $cfg['charset']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
}
