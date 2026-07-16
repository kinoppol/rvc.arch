<?php
declare(strict_types=1);

/**
 * Login, logout and public self-registration.
 */
final class AuthController
{
    /** Roles a self-registering user may pick (never admin). */
    private const PUBLIC_ROLES = ['ครู', 'ตัวแทนนักศึกษา'];

    private Repository $repo;

    public function __construct()
    {
        $this->repo = new Repository();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('admin');
        }
        App::render('auth/login', ['email' => ''], [
            'section' => 'auth', 'title' => 'เข้าสู่ระบบ', 'bare' => true,
        ]);
    }

    public function login(): void
    {
        verify_csrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $result = Auth::attempt($email, $password);

        if ($result === 'ok') {
            $intended = $_SESSION['_intended'] ?? '';
            unset($_SESSION['_intended']);
            flash('ยินดีต้อนรับ ' . (Auth::user()['name'] ?? ''));
            if ($intended !== '') {
                header('Location: ' . $intended);
                exit;
            }
            redirect('admin');
        }

        flash(match ($result) {
            'pending'   => 'บัญชีของคุณอยู่ระหว่างรอการอนุมัติจากผู้ดูแลระบบ',
            'suspended' => 'บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            default      => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        }, 'error');

        App::render('auth/login', ['email' => $email], [
            'section' => 'auth', 'title' => 'เข้าสู่ระบบ', 'bare' => true,
        ]);
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        flash('ออกจากระบบแล้ว');
        redirect('');
    }

    /* ---------------- Self-registration ---------------- */

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('admin');
        }
        App::render('auth/register', [
            'old'   => ['name' => '', 'email' => '', 'role' => 'ครู', 'dept' => ''],
            'roles' => self::PUBLIC_ROLES,
            'depts' => App::DEPTS,
        ], ['section' => 'auth', 'title' => 'สมัครสมาชิก', 'bare' => true]);
    }

    public function register(): void
    {
        verify_csrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $role = (string) ($_POST['role'] ?? '');
        $dept = trim((string) ($_POST['dept'] ?? '')) ?: null;
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password2'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'กรุณากรอกชื่อ-สกุล';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'อีเมลไม่ถูกต้อง';
        }
        if (!in_array($role, self::PUBLIC_ROLES, true)) {
            $errors[] = 'กรุณาเลือกสถานะผู้ใช้ (ครู หรือ นักศึกษา)';
        }
        if (strlen($password) < 6) {
            $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }
        if ($password !== $password2) {
            $errors[] = 'รหัสผ่านและการยืนยันไม่ตรงกัน';
        }
        if (!$errors && $this->repo->findUserByEmail($email)) {
            $errors[] = 'อีเมลนี้ถูกใช้งานแล้ว';
        }

        if ($errors) {
            foreach ($errors as $e) {
                flash($e, 'error');
            }
            App::render('auth/register', [
                'old'   => ['name' => $name, 'email' => $email, 'role' => $role, 'dept' => (string) $dept],
                'roles' => self::PUBLIC_ROLES,
                'depts' => App::DEPTS,
            ], ['section' => 'auth', 'title' => 'สมัครสมาชิก', 'bare' => true]);
            return;
        }

        $status = $this->repo->registerUser($name, $email, $password, $role, $dept);
        flash($status === 'pending'
            ? 'สมัครสมาชิกสำเร็จ — บัญชีของคุณอยู่ระหว่างรอผู้ดูแลระบบอนุมัติ'
            : 'สมัครสมาชิกสำเร็จ เข้าสู่ระบบได้ทันที');
        redirect('login');
    }
}
