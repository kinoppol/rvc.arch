<?php
declare(strict_types=1);

/**
 * Admin area — requires the admin role (enforced in the router).
 */
final class AdminController
{
    use ResearchFormTrait;

    private Repository $repo;

    public function __construct()
    {
        $this->repo = new Repository();
    }

    private function layoutVars(string $view, string $title): array
    {
        return ['section' => 'admin', 'adminView' => $view, 'title' => $title];
    }

    /* ---------------- Dashboard ---------------- */

    public function dashboard(): void
    {
        $counts = $this->repo->counts();
        $catCounts = $this->repo->countsByCategory();
        $maxCat = max(1, ...array_map(fn ($c) => $c['count'], $catCounts) ?: [1]);
        $catBars = array_map(fn ($c) => [
            'name'  => $c['name'],
            'count' => $c['count'],
            'color' => $c['color'],
            'pct'   => round($c['count'] / $maxCat * 100) . '%',
        ], $catCounts);

        $yearCounts = $this->repo->countsByYear();
        $maxYear = max(1, ...array_map(fn ($y) => $y['count'], $yearCounts) ?: [1]);
        $yearBars = array_map(fn ($y) => [
            'year'  => $y['year'],
            'count' => $y['count'],
            'pct'   => round($y['count'] / $maxYear * 100) . '%',
        ], $yearCounts);

        App::render('admin/dashboard', [
            'counts'   => $counts,
            'catBars'  => $catBars,
            'yearBars' => $yearBars,
            'pending'  => $this->repo->pending(),
        ], $this->layoutVars('dashboard', 'แดชบอร์ด'));
    }

    /* ---------------- Manage research ---------------- */

    public function research(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $status = (string) ($_GET['status'] ?? '');
        App::render('admin/research', [
            'list'   => $this->repo->manageList($q, $status),
            'q'      => $q,
            'status' => $status,
        ], $this->layoutVars('research', 'จัดการงานวิจัย'));
    }

    public function approve(array $p): void
    {
        verify_csrf();
        $this->repo->setStatus((int) $p['id'], 'เผยแพร่');
        flash('อนุมัติเผยแพร่งานวิจัยแล้ว');
        redirect_back(url('admin'));
    }

    public function cycleStatus(array $p): void
    {
        verify_csrf();
        $this->repo->cycleStatus((int) $p['id']);
        redirect_back(url('admin/research'));
    }

    public function deleteResearch(array $p): void
    {
        verify_csrf();
        $this->repo->delete((int) $p['id']);
        flash('ลบงานวิจัยแล้ว');
        redirect_back(url('admin/research'));
    }

    /* ---------------- Submit / edit form ---------------- */

    public function submitForm(array $p = []): void
    {
        $editing = null;
        if (!empty($p['id'])) {
            $editing = $this->repo->find((int) $p['id']);
            if (!$editing) {
                flash('ไม่พบงานวิจัยที่ต้องการแก้ไข', 'error');
                redirect('admin/research');
            }
        }
        App::render('submit', [
            'editing'     => $editing,
            'enabledCats' => $this->repo->categories(true),
            'depts'       => App::DEPTS,
            'chapters'    => App::CHAPTER_NAMES,
            'area'        => 'admin',
            'statuses'    => App::STATUSES,
        ], $this->layoutVars('submit', $editing ? 'แก้ไขงานวิจัย' : 'นำเข้างานวิจัย'));
    }

    public function submit(array $p = []): void
    {
        verify_csrf();
        $id = !empty($p['id']) ? (int) $p['id'] : null;

        $data = $this->buildResearchData(App::STATUSES, 'แบบร่าง', 'admin/submit');
        $newId = $this->repo->save($data, $id);
        $this->storeUploads($newId);

        flash($id ? 'บันทึกการแก้ไขงานวิจัยแล้ว' : 'บันทึกงานวิจัยเรียบร้อยแล้ว');
        redirect('admin/research');
    }

    /* ---------------- Categories ---------------- */

    public function categories(): void
    {
        App::render('admin/categories', [
            'cats' => $this->repo->categories(),
        ], $this->layoutVars('categories', 'จัดการประเภทงานวิจัย'));
    }

    public function addCategory(): void
    {
        verify_csrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name !== '') {
            $this->repo->addCategory($name);
            flash('เพิ่มประเภทงานวิจัยแล้ว');
        }
        redirect('admin/categories');
    }

    public function renameCategory(array $p): void
    {
        verify_csrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name !== '') {
            $this->repo->renameCategory((int) $p['id'], $name);
            flash('แก้ไขชื่อประเภทแล้ว');
        }
        redirect('admin/categories');
    }

    public function toggleCategory(array $p): void
    {
        verify_csrf();
        $this->repo->toggleCategory((int) $p['id']);
        redirect('admin/categories');
    }

    public function deleteCategory(array $p): void
    {
        verify_csrf();
        $this->repo->deleteCategory((int) $p['id']);
        flash('ลบประเภทงานวิจัยแล้ว');
        redirect('admin/categories');
    }

    /* ---------------- Users ---------------- */

    public function users(): void
    {
        App::render('admin/users', [
            'users'           => $this->repo->users(),
            'depts'           => App::DEPTS,
            'roles'           => ['ผู้ดูแลระบบ', 'ครู', 'ตัวแทนนักศึกษา'],
            'requireApproval' => $this->repo->requireApproval(),
            'pendingCount'    => $this->repo->pendingUsersCount(),
        ], $this->layoutVars('users', 'จัดการผู้ใช้งาน'));
    }

    /** Toggle whether new self-registrations need admin approval. */
    public function updateSettings(): void
    {
        verify_csrf();
        $this->repo->setSetting('require_approval', isset($_POST['require_approval']) ? '1' : '0');
        flash('บันทึกการตั้งค่าแล้ว');
        redirect('admin/users');
    }

    public function addUser(): void
    {
        verify_csrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? 'ครู');
        $dept = trim((string) ($_POST['dept'] ?? '')) ?: null;

        if ($name === '' || $email === '' || strlen($password) < 6) {
            flash('กรอกชื่อ อีเมล และรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)', 'error');
            redirect('admin/users');
        }
        if ($this->repo->findUserByEmail($email)) {
            flash('อีเมลนี้ถูกใช้งานแล้ว', 'error');
            redirect('admin/users');
        }
        $this->repo->addUser($name, $email, $password, $role, $dept);
        flash('เพิ่มผู้ใช้งานแล้ว');
        redirect('admin/users');
    }

    public function approveUser(array $p): void
    {
        verify_csrf();
        $this->repo->setUserStatus((int) $p['id'], 'approved');
        flash('อนุมัติ/เปิดใช้งานบัญชีผู้ใช้แล้ว');
        redirect('admin/users');
    }

    public function suspendUser(array $p): void
    {
        verify_csrf();
        if ((int) $p['id'] === (int) (Auth::user()['id'] ?? 0)) {
            flash('ไม่สามารถระงับบัญชีของตนเองได้', 'error');
            redirect('admin/users');
        }
        $this->repo->setUserStatus((int) $p['id'], 'suspended');
        flash('ระงับบัญชีผู้ใช้แล้ว');
        redirect('admin/users');
    }
}
