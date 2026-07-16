<?php
declare(strict_types=1);

/**
 * Shared handling of the research submit/edit form — used by both the admin
 * area (full control) and the member area (own research, limited statuses).
 */
trait ResearchFormTrait
{
    /**
     * Build the research data array from $_POST. Validates title/category and
     * redirects back to $submitFallback on error.
     *
     * @param string[] $allowedStatuses statuses this user is permitted to set
     */
    protected function buildResearchData(array $allowedStatuses, string $defaultStatus, string $submitFallback): array
    {
        $titleTh = trim((string) ($_POST['title_th'] ?? ''));
        $catId = (string) ($_POST['category_id'] ?? '');
        if ($titleTh === '' || $catId === '') {
            flash('กรุณากรอกชื่อเรื่องและเลือกประเภทงานวิจัย', 'error');
            redirect_back(url($submitFallback));
        }

        $authorsNames = (array) ($_POST['author_name'] ?? []);
        $authorsRoles = (array) ($_POST['author_role'] ?? []);
        $authors = [];
        foreach ($authorsNames as $i => $name) {
            $authors[] = ['name' => trim((string) $name), 'role' => (string) ($authorsRoles[$i] ?? 'ผู้วิจัยร่วม')];
        }
        if (!array_filter($authors, fn ($a) => $a['name'] !== '')) {
            $authors = [['name' => 'ไม่ระบุ', 'role' => 'ผู้วิจัยหลัก']];
        }

        $keywords = array_values(array_filter(
            array_map('trim', (array) ($_POST['keywords'] ?? [])),
            fn ($k) => $k !== ''
        ));

        $status = in_array($_POST['status'] ?? '', $allowedStatuses, true) ? (string) $_POST['status'] : $defaultStatus;

        return [
            'category_id'   => (int) $catId,
            'dept'          => trim((string) ($_POST['dept'] ?? '')) ?: null,
            'title_th'      => $titleTh,
            'title_en'      => trim((string) ($_POST['title_en'] ?? '')) ?: null,
            'abstract_th'   => trim((string) ($_POST['abstract_th'] ?? '')),
            'abstract_en'   => trim((string) ($_POST['abstract_en'] ?? '')),
            'pub_year'      => (int) ($_POST['pub_year'] ?? 0) ?: null,
            'academic_year' => (int) ($_POST['academic_year'] ?? 0) ?: null,
            'status'        => $status,
            'authors'       => $authors,
            'keywords'      => $keywords,
            'created_by'    => Auth::user()['id'] ?? null, // only applied on insert
        ];
    }

    /** Store uploaded chapter PDFs and their public/locked flags. */
    protected function storeUploads(int $researchId): void
    {
        $this->repo->ensureChapterRows($researchId);
        $uploadDir = App::config('app')['upload_dir'];
        $maxBytes = App::config('app')['max_upload_bytes'];
        $publicFlags = (array) ($_POST['chapter_public'] ?? []);

        foreach (App::CHAPTER_NAMES as $idx => $name) {
            $this->repo->setFilePublic($researchId, $idx, isset($publicFlags[$idx]));

            if (empty($_FILES['chapter']['name'][$idx])) {
                continue;
            }
            if (($_FILES['chapter']['error'][$idx] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $tmp = $_FILES['chapter']['tmp_name'][$idx];
            $size = (int) $_FILES['chapter']['size'][$idx];
            $orig = (string) $_FILES['chapter']['name'][$idx];

            if ($size > $maxBytes) {
                flash("ไฟล์ \"{$orig}\" มีขนาดเกินกำหนด", 'error');
                continue;
            }
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $mime = function_exists('mime_content_type') ? mime_content_type($tmp) : 'application/pdf';
            if ($ext !== 'pdf' || ($mime && stripos($mime, 'pdf') === false)) {
                flash("รองรับเฉพาะไฟล์ PDF (\"{$orig}\")", 'error');
                continue;
            }

            $stored = sprintf('r%d_ch%d_%s.pdf', $researchId, $idx, bin2hex(random_bytes(6)));
            if (move_uploaded_file($tmp, $uploadDir . '/' . $stored)) {
                $this->repo->setFile($researchId, $idx, $stored, $orig, $size);
            }
        }
    }
}
