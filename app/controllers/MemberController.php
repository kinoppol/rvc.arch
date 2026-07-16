<?php
declare(strict_types=1);

/**
 * Member area (/my) for teachers & students — submit and manage ONLY their own
 * research. Requires login (enforced in the router). Members cannot publish;
 * they save drafts or submit for review, and an admin approves/publishes.
 */
final class MemberController
{
    use ResearchFormTrait;

    /** Statuses a member is allowed to set on their own research. */
    private const MEMBER_STATUSES = ['แบบร่าง', 'รอตรวจสอบ'];

    private Repository $repo;

    public function __construct()
    {
        $this->repo = new Repository();
    }

    private function layoutVars(string $view, string $title): array
    {
        return ['section' => 'member', 'memberView' => $view, 'title' => $title];
    }

    public function dashboard(): void
    {
        $uid = (int) (Auth::user()['id'] ?? 0);
        App::render('member/dashboard', [
            'list' => $this->repo->researchByUser($uid),
        ], $this->layoutVars('home', 'งานวิจัยของฉัน'));
    }

    public function submitForm(array $p = []): void
    {
        $editing = null;
        if (!empty($p['id'])) {
            $editing = $this->ownedOrRedirect((int) $p['id']);
            if (!$this->isEditable($editing)) {
                flash('งานวิจัยที่เผยแพร่แล้วไม่สามารถแก้ไขได้ กรุณาติดต่อผู้ดูแลระบบ', 'error');
                redirect('my');
            }
        }
        App::render('submit', [
            'editing'     => $editing,
            'enabledCats' => $this->repo->categories(true),
            'depts'       => App::DEPTS,
            'chapters'    => App::CHAPTER_NAMES,
            'area'        => 'my',
            'statuses'    => self::MEMBER_STATUSES,
        ], $this->layoutVars('submit', $editing ? 'แก้ไขงานวิจัย' : 'ส่งงานวิจัย'));
    }

    public function submit(array $p = []): void
    {
        verify_csrf();
        $id = null;
        if (!empty($p['id'])) {
            $existing = $this->ownedOrRedirect((int) $p['id']);
            if (!$this->isEditable($existing)) {
                flash('งานวิจัยที่เผยแพร่แล้วไม่สามารถแก้ไขได้', 'error');
                redirect('my');
            }
            $id = (int) $p['id'];
        }

        $data = $this->buildResearchData(self::MEMBER_STATUSES, 'แบบร่าง', 'my/submit');
        $newId = $this->repo->save($data, $id);
        $this->storeUploads($newId);

        flash($id ? 'บันทึกการแก้ไขงานวิจัยแล้ว' : 'ส่งงานวิจัยเรียบร้อยแล้ว');
        redirect('my');
    }

    public function deleteResearch(array $p): void
    {
        verify_csrf();
        $r = $this->ownedOrRedirect((int) $p['id']);
        if ($r['status'] === 'เผยแพร่') {
            flash('งานวิจัยที่เผยแพร่แล้วไม่สามารถลบได้ กรุณาติดต่อผู้ดูแลระบบ', 'error');
            redirect('my');
        }
        $this->repo->delete((int) $p['id']);
        flash('ลบงานวิจัยแล้ว');
        redirect('my');
    }

    /** Fetch a research record that must belong to the current user, else bail. */
    private function ownedOrRedirect(int $id): array
    {
        $r = $this->repo->find($id);
        $me = (int) (Auth::user()['id'] ?? 0);
        if (!$r || (int) ($r['created_by'] ?? 0) !== $me) {
            flash('ไม่พบงานวิจัย หรือคุณไม่มีสิทธิ์เข้าถึงรายการนี้', 'error');
            redirect('my');
        }
        return $r;
    }

    private function isEditable(array $r): bool
    {
        return in_array($r['status'], self::MEMBER_STATUSES, true);
    }
}
