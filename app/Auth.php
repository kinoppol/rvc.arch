<?php
declare(strict_types=1);

/**
 * Session-based authentication for the admin area.
 */
final class Auth
{
    /**
     * Try to sign a user in.
     *
     * @return string one of: 'ok' | 'invalid' | 'pending' | 'suspended'
     */
    public static function attempt(string $email, string $password): string
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return 'invalid';
        }
        if ($user['status'] === 'pending') {
            return 'pending';
        }
        if ($user['status'] !== 'approved') {
            return 'suspended';
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'dept'  => $user['dept'],
        ];
        return 'ok';
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'ผู้ดูแลระบบ';
    }

    /** Landing route for the current user after login. */
    public static function homeUrl(): string
    {
        return self::isAdmin() ? 'admin' : 'my';
    }

    /** Redirect guests to the login page. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '';
            flash('กรุณาเข้าสู่ระบบก่อนเข้าใช้งาน', 'error');
            redirect('login');
        }
    }

    /** Require the admin role; members are sent to their own area. */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            flash('เฉพาะผู้ดูแลระบบเท่านั้นที่เข้าถึงส่วนนี้ได้', 'error');
            redirect('my');
        }
    }
}
