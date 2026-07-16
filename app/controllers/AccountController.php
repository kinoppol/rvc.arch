<?php
declare(strict_types=1);

/**
 * "My account" — change own password. Available to any signed-in user;
 * rendered inside the admin or member chrome depending on the role.
 */
final class AccountController
{
    private Repository $repo;

    public function __construct()
    {
        $this->repo = new Repository();
    }

    private function layoutVars(): array
    {
        return Auth::isAdmin()
            ? ['section' => 'admin', 'adminView' => 'account', 'title' => 'บัญชีของฉัน']
            : ['section' => 'member', 'memberView' => 'account', 'title' => 'บัญชีของฉัน'];
    }

    public function show(): void
    {
        App::render('account', [], $this->layoutVars());
    }

    public function changePassword(): void
    {
        verify_csrf();
        $uid     = (int) (Auth::user()['id'] ?? 0);
        $current = (string) ($_POST['current'] ?? '');
        $new     = (string) ($_POST['new'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');

        $user = $this->repo->findUserById($uid);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            flash('รหัสผ่านปัจจุบันไม่ถูกต้อง', 'error');
            redirect('account');
        }
        if (strlen($new) < 6) {
            flash('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร', 'error');
            redirect('account');
        }
        if ($new !== $confirm) {
            flash('รหัสผ่านใหม่และการยืนยันไม่ตรงกัน', 'error');
            redirect('account');
        }

        $this->repo->updatePassword($uid, $new);
        flash('เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
        redirect('account');
    }
}
