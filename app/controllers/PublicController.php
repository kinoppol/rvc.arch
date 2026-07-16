<?php
declare(strict_types=1);

/**
 * Public-facing site: home, search, research detail, file download.
 */
final class PublicController
{
    private Repository $repo;

    public function __construct()
    {
        $this->repo = new Repository();
    }

    public function home(): void
    {
        $counts = $this->repo->counts();
        App::render('public/home', [
            'stats'    => [
                'total' => $counts['published'],
                'cats'  => $this->repo->categories(true),
            ],
            'latest'   => $this->repo->latestPublished(6),
        ], [
            'section'    => 'public',
            'publicView' => 'home',
            'title'      => 'หน้าแรก',
        ]);
    }

    public function search(): void
    {
        $f = [
            'q'    => trim((string) ($_GET['q'] ?? '')),
            'cat'  => (string) ($_GET['cat'] ?? ''),
            'dept' => (string) ($_GET['dept'] ?? ''),
            'year' => (string) ($_GET['year'] ?? ''),
            'sort' => (string) ($_GET['sort'] ?? 'new'),
        ];
        App::render('public/search', [
            'filters'      => $f,
            'results'      => $this->repo->search($f),
            'enabledCats'  => $this->repo->categories(true),
            'depts'        => App::DEPTS,
            'years'        => $this->repo->years(),
        ], [
            'section'    => 'public',
            'publicView' => 'search',
            'title'      => 'สืบค้นงานวิจัย',
        ]);
    }

    public function detail(array $params): void
    {
        $research = $this->repo->find((int) $params['id']);
        if (!$research) {
            http_response_code(404);
            App::render('public/not_found', [], [
                'section' => 'public', 'publicView' => 'search', 'title' => 'ไม่พบงานวิจัย',
            ]);
            return;
        }
        // Unpublished records are visible only to an admin or the owner.
        if ($research['status'] !== 'เผยแพร่') {
            $isOwner = Auth::check() && (int) ($research['created_by'] ?? 0) === (int) (Auth::user()['id'] ?? -1);
            if (!Auth::isAdmin() && !$isOwner) {
                http_response_code(404);
                App::render('public/not_found', [], [
                    'section' => 'public', 'publicView' => 'search', 'title' => 'ไม่พบงานวิจัย',
                ]);
                return;
            }
        }
        App::render('public/detail', [
            'current' => $research,
        ], [
            'section'    => 'public',
            'publicView' => 'detail',
            'title'      => $research['title_th'],
        ]);
    }

    /** Stream a chapter PDF, enforcing public/locked access. */
    public function download(array $params): void
    {
        $file = $this->repo->fileById((int) $params['id']);
        if (!$file || !$file['uploaded'] || !$file['stored_name']) {
            http_response_code(404);
            exit('ไม่พบไฟล์');
        }
        // Locked files: only an admin or the research owner may download.
        if (!$file['is_public']) {
            $research = $this->repo->find((int) $file['research_id']);
            $isOwner = Auth::check() && (int) ($research['created_by'] ?? 0) === (int) (Auth::user()['id'] ?? -1);
            if (!Auth::isAdmin() && !$isOwner) {
                http_response_code(403);
                exit('ไฟล์นี้ไม่เปิดเผยต่อสาธารณะ');
            }
        }

        $path = App::config('app')['upload_dir'] . '/' . $file['stored_name'];
        if (!is_file($path)) {
            http_response_code(404);
            exit('ไฟล์ต้นฉบับหายไปจากระบบ');
        }

        $download = $file['original_name'] ?: ($file['chapter_name'] . '.pdf');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . rawurlencode($download) . '"');
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }
}
