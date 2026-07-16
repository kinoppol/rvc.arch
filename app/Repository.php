<?php
declare(strict_types=1);

/**
 * Data access for categories, research, authors, keywords, files and users.
 * Returns plain arrays; view-layer decoration (colours, initials) is done in
 * decorate() so both public and admin screens stay consistent with the design.
 */
final class Repository
{
    private function db(): PDO
    {
        return Database::pdo();
    }

    /* =====================================================
     *  Categories
     * ===================================================== */

    /** All categories ordered, each with a stable accent colour. */
    public function categories(bool $enabledOnly = false): array
    {
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM research r WHERE r.category_id = c.id) AS count
                FROM categories c';
        if ($enabledOnly) {
            $sql .= ' WHERE c.enabled = 1';
        }
        $sql .= ' ORDER BY c.sort_order, c.id';
        $rows = $this->db()->query($sql)->fetchAll();

        foreach ($rows as $i => &$c) {
            $c['id']      = (int) $c['id'];
            $c['enabled'] = (bool) $c['enabled'];
            $c['count']   = (int) $c['count'];
            $c['color']   = category_color($i);
        }
        return $rows;
    }

    /** Map of category id => accent colour (position based). */
    public function categoryColorMap(): array
    {
        $map = [];
        foreach ($this->categories() as $c) {
            $map[$c['id']] = $c['color'];
        }
        return $map;
    }

    public function addCategory(string $name): void
    {
        $max = (int) $this->db()->query('SELECT COALESCE(MAX(sort_order),0) FROM categories')->fetchColumn();
        $stmt = $this->db()->prepare('INSERT INTO categories (name, enabled, sort_order) VALUES (?, 1, ?)');
        $stmt->execute([$name, $max + 1]);
    }

    public function renameCategory(int $id, string $name): void
    {
        $this->db()->prepare('UPDATE categories SET name = ? WHERE id = ?')->execute([$name, $id]);
    }

    public function toggleCategory(int $id): void
    {
        $this->db()->prepare('UPDATE categories SET enabled = 1 - enabled WHERE id = ?')->execute([$id]);
    }

    public function deleteCategory(int $id): void
    {
        // research.category_id is ON DELETE SET NULL, so records survive.
        $this->db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    }

    /* =====================================================
     *  Research — reading
     * ===================================================== */

    /** Attach display fields (category name/colour, status badge, lead author…). */
    public function decorate(array $r, array $colorMap): array
    {
        $r['id']            = (int) $r['id'];
        $r['category_id']   = $r['category_id'] !== null ? (int) $r['category_id'] : null;
        $r['pub_year']      = (int) $r['pub_year'];
        $r['academic_year'] = (int) $r['academic_year'];
        $r['catName']       = $r['category_name'] ?? '—';
        $r['color']         = $colorMap[$r['category_id']] ?? 'var(--c1)';
        [$sb, $sf]          = status_meta($r['status']);
        $r['statusBg']      = $sb;
        $r['statusFg']      = $sf;

        $authors            = $this->authors($r['id']);
        $r['authors']       = $authors;
        $lead               = $authors[0]['name'] ?? '';
        $r['leadAuthor']    = $lead;
        $r['authorInitial'] = name_initial($lead);
        $r['authorsLine']   = implode(', ', array_column($authors, 'name'));
        $r['abstractShort'] = excerpt($r['abstract_th'], 90);
        $r['pubYearShort']  = mb_substr((string) $r['pub_year'], -2);
        return $r;
    }

    private function baseSelect(): string
    {
        return 'SELECT r.*, c.name AS category_name
                FROM research r
                LEFT JOIN categories c ON c.id = r.category_id';
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db()->prepare($this->baseSelect() . ' WHERE r.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $decorated = $this->decorate($row, $this->categoryColorMap());
        $decorated['keywords'] = $this->keywords($id);
        $decorated['files']    = $this->files($id);
        return $decorated;
    }

    /** Latest published research for the home page. */
    public function latestPublished(int $limit = 6): array
    {
        $map = $this->categoryColorMap();
        $stmt = $this->db()->prepare(
            $this->baseSelect() . " WHERE r.status = 'เผยแพร่'
             ORDER BY r.pub_year DESC, r.id DESC LIMIT " . (int) $limit
        );
        $stmt->execute();
        return array_map(fn ($r) => $this->decorate($r, $map), $stmt->fetchAll());
    }

    /**
     * Public search over published research.
     *
     * @param array{q?:string,cat?:string,dept?:string,year?:string,sort?:string} $f
     */
    public function search(array $f): array
    {
        $map = $this->categoryColorMap();
        $where = ["r.status = 'เผยแพร่'"];
        $args = [];

        if (!empty($f['cat'])) {
            $where[] = 'r.category_id = ?';
            $args[] = (int) $f['cat'];
        }
        if (!empty($f['dept'])) {
            $where[] = 'r.dept = ?';
            $args[] = $f['dept'];
        }
        if (!empty($f['year'])) {
            $where[] = 'r.pub_year = ?';
            $args[] = (int) $f['year'];
        }
        if (!empty($f['q'])) {
            $where[] = '(r.title_th LIKE ? OR r.title_en LIKE ? OR r.abstract_th LIKE ?
                        OR r.id IN (SELECT research_id FROM research_authors WHERE name LIKE ?)
                        OR r.id IN (SELECT research_id FROM research_keywords WHERE keyword LIKE ?))';
            $like = '%' . $f['q'] . '%';
            array_push($args, $like, $like, $like, $like, $like);
        }

        $order = match ($f['sort'] ?? 'new') {
            'old'   => 'r.pub_year ASC, r.id ASC',
            'az'    => 'r.title_th ASC',
            default => 'r.pub_year DESC, r.id DESC',
        };

        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $order;
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($args);
        return array_map(fn ($r) => $this->decorate($r, $map), $stmt->fetchAll());
    }

    /** Admin management list (all statuses). */
    public function manageList(string $q = '', string $status = ''): array
    {
        $map = $this->categoryColorMap();
        $where = [];
        $args = [];
        if ($status !== '') {
            $where[] = 'r.status = ?';
            $args[] = $status;
        }
        if ($q !== '') {
            $where[] = '(r.title_th LIKE ? OR r.dept LIKE ?
                        OR r.id IN (SELECT research_id FROM research_authors WHERE name LIKE ?))';
            $like = '%' . $q . '%';
            array_push($args, $like, $like, $like);
        }
        $sql = $this->baseSelect();
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY r.updated_at DESC, r.id DESC';
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($args);
        return array_map(fn ($r) => $this->decorate($r, $map), $stmt->fetchAll());
    }

    public function pending(): array
    {
        return $this->manageList('', 'รอตรวจสอบ');
    }

    public function authors(int $researchId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT name, role FROM research_authors WHERE research_id = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$researchId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$a) {
            $a['initial'] = name_initial($a['name']);
        }
        return $rows;
    }

    public function keywords(int $researchId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT keyword FROM research_keywords WHERE research_id = ? ORDER BY sort_order, id'
        );
        $stmt->execute([$researchId]);
        return array_column($stmt->fetchAll(), 'keyword');
    }

    public function files(int $researchId): array
    {
        $stmt = $this->db()->prepare(
            'SELECT * FROM research_files WHERE research_id = ? ORDER BY chapter_index'
        );
        $stmt->execute([$researchId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$f) {
            $f['id']        = (int) $f['id'];
            $f['uploaded']  = (bool) $f['uploaded'];
            $f['is_public'] = (bool) $f['is_public'];
        }
        return $rows;
    }

    /* =====================================================
     *  Research — writing
     * ===================================================== */

    /**
     * Insert or update a research record with its authors, keywords and file rows.
     *
     * @param array<string,mixed> $data
     * @return int research id
     */
    public function save(array $data, ?int $id = null): int
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            if ($id) {
                $stmt = $db->prepare(
                    'UPDATE research SET category_id=?, dept=?, title_th=?, title_en=?,
                        abstract_th=?, abstract_en=?, pub_year=?, academic_year=?, status=?
                     WHERE id=?'
                );
                $stmt->execute([
                    $data['category_id'], $data['dept'], $data['title_th'], $data['title_en'],
                    $data['abstract_th'], $data['abstract_en'], $data['pub_year'],
                    $data['academic_year'], $data['status'], $id,
                ]);
                $db->prepare('DELETE FROM research_authors  WHERE research_id=?')->execute([$id]);
                $db->prepare('DELETE FROM research_keywords WHERE research_id=?')->execute([$id]);
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO research (category_id, dept, title_th, title_en, abstract_th,
                        abstract_en, pub_year, academic_year, status, created_by)
                     VALUES (?,?,?,?,?,?,?,?,?,?)'
                );
                $stmt->execute([
                    $data['category_id'], $data['dept'], $data['title_th'], $data['title_en'],
                    $data['abstract_th'], $data['abstract_en'], $data['pub_year'],
                    $data['academic_year'], $data['status'], $data['created_by'] ?? null,
                ]);
                $id = (int) $db->lastInsertId();
            }

            $ai = $db->prepare('INSERT INTO research_authors (research_id, name, role, sort_order) VALUES (?,?,?,?)');
            foreach ($data['authors'] as $i => $a) {
                if (trim($a['name']) === '') {
                    continue;
                }
                $ai->execute([$id, $a['name'], $a['role'], $i]);
            }

            $ki = $db->prepare('INSERT INTO research_keywords (research_id, keyword, sort_order) VALUES (?,?,?)');
            foreach ($data['keywords'] as $i => $kw) {
                if (trim($kw) === '') {
                    continue;
                }
                $ki->execute([$id, $kw, $i]);
            }

            $this->ensureChapterRows($id);
            $db->commit();
            return $id;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Guarantee one research_files row per chapter slot. */
    public function ensureChapterRows(int $researchId): void
    {
        $existing = $this->db()->prepare('SELECT chapter_index FROM research_files WHERE research_id = ?');
        $existing->execute([$researchId]);
        $have = array_column($existing->fetchAll(), 'chapter_index');

        $ins = $this->db()->prepare(
            'INSERT INTO research_files (research_id, chapter_index, chapter_name, is_public, uploaded)
             VALUES (?,?,?,?,0)'
        );
        foreach (App::CHAPTER_NAMES as $idx => $name) {
            if (!in_array($idx, array_map('intval', $have), true)) {
                $ins->execute([$researchId, $idx, $name, $idx < 2 ? 1 : 0]);
            }
        }
    }

    public function setFile(int $researchId, int $chapterIndex, string $stored, string $original, int $size): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE research_files
                SET stored_name=?, original_name=?, size_bytes=?, uploaded=1
              WHERE research_id=? AND chapter_index=?'
        );
        $stmt->execute([$stored, $original, $size, $researchId, $chapterIndex]);
    }

    public function setFilePublic(int $researchId, int $chapterIndex, bool $public): void
    {
        $stmt = $this->db()->prepare(
            'UPDATE research_files SET is_public=? WHERE research_id=? AND chapter_index=?'
        );
        $stmt->execute([$public ? 1 : 0, $researchId, $chapterIndex]);
    }

    public function fileById(int $fileId): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM research_files WHERE id = ?');
        $stmt->execute([$fileId]);
        return $stmt->fetch() ?: null;
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->prepare('UPDATE research SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public function cycleStatus(int $id): void
    {
        $cur = $this->db()->prepare('SELECT status FROM research WHERE id = ?');
        $cur->execute([$id]);
        $status = (string) $cur->fetchColumn();
        $order = App::STATUSES; // เผยแพร่, รอตรวจสอบ, แบบร่าง, ไม่เผยแพร่
        // design cycle order: แบบร่าง -> รอตรวจสอบ -> เผยแพร่ -> ไม่เผยแพร่ -> ...
        $cycle = ['แบบร่าง', 'รอตรวจสอบ', 'เผยแพร่', 'ไม่เผยแพร่'];
        $pos = array_search($status, $cycle, true);
        $next = $cycle[($pos === false ? 0 : $pos + 1) % count($cycle)];
        $this->setStatus($id, $next);
    }

    public function delete(int $id): void
    {
        // Remove stored files from disk first.
        foreach ($this->files($id) as $f) {
            if ($f['stored_name']) {
                @unlink(App::config('app')['upload_dir'] . '/' . $f['stored_name']);
            }
        }
        $this->db()->prepare('DELETE FROM research WHERE id = ?')->execute([$id]);
    }

    /* =====================================================
     *  Dashboard aggregates
     * ===================================================== */

    public function counts(): array
    {
        $db = $this->db();
        return [
            'total'     => (int) $db->query('SELECT COUNT(*) FROM research')->fetchColumn(),
            'published' => (int) $db->query("SELECT COUNT(*) FROM research WHERE status='เผยแพร่'")->fetchColumn(),
            'pending'   => (int) $db->query("SELECT COUNT(*) FROM research WHERE status='รอตรวจสอบ'")->fetchColumn(),
            'users'     => (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        ];
    }

    /** Research count per category (all categories). */
    public function countsByCategory(): array
    {
        $rows = $this->categories(); // includes count + colour
        return $rows;
    }

    /** Research count per publication year, ascending. */
    public function countsByYear(): array
    {
        $rows = $this->db()->query(
            'SELECT pub_year AS year, COUNT(*) AS count
             FROM research WHERE pub_year IS NOT NULL
             GROUP BY pub_year ORDER BY pub_year'
        )->fetchAll();
        foreach ($rows as &$r) {
            $r['year']  = (int) $r['year'];
            $r['count'] = (int) $r['count'];
        }
        return $rows;
    }

    /** Distinct publication years present (for the search filter). */
    public function years(): array
    {
        $rows = $this->db()->query(
            'SELECT DISTINCT pub_year FROM research WHERE pub_year IS NOT NULL ORDER BY pub_year DESC'
        )->fetchAll();
        return array_map('intval', array_column($rows, 'pub_year'));
    }

    /* =====================================================
     *  Users
     * ===================================================== */

    /** Users list, pending ones first so they are easy to act on. */
    public function users(): array
    {
        $rows = $this->db()->query(
            "SELECT u.*, (SELECT COUNT(*) FROM research r WHERE r.created_by = u.id) AS count
             FROM users u
             ORDER BY (u.status = 'pending') DESC, u.id"
        )->fetchAll();
        foreach ($rows as &$u) {
            $u['id']          = (int) $u['id'];
            $u['count']       = (int) $u['count'];
            $u['initial']     = name_initial($u['name']);
            [$b, $f]          = role_meta($u['role']);
            $u['roleBg']      = $b;
            $u['roleFg']      = $f;
            [$sl, $sb, $sf]   = user_status_meta($u['status']);
            $u['statusLabel'] = $sl;
            $u['statusBg']    = $sb;
            $u['statusFg']    = $sf;
        }
        return $rows;
    }

    public function pendingUsersCount(): int
    {
        return (int) $this->db()->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /** Admin-created account — approved immediately. */
    public function addUser(string $name, string $email, string $password, string $role, ?string $dept): void
    {
        $this->createUser($name, $email, $password, $role, $dept, 'approved');
    }

    /** Self-registration — status depends on the require_approval setting. */
    public function registerUser(string $name, string $email, string $password, string $role, ?string $dept): string
    {
        $status = $this->requireApproval() ? 'pending' : 'approved';
        $this->createUser($name, $email, $password, $role, $dept, $status);
        return $status;
    }

    private function createUser(string $name, string $email, string $password, string $role, ?string $dept, string $status): void
    {
        $stmt = $this->db()->prepare(
            'INSERT INTO users (name, email, password_hash, role, dept, status)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $dept, $status]);
    }

    public function setUserStatus(int $id, string $status): void
    {
        $this->db()->prepare('UPDATE users SET status = ? WHERE id = ?')->execute([$status, $id]);
    }

    public function findUserById(int $id): ?array
    {
        $stmt = $this->db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $this->db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    /* =====================================================
     *  Settings (key/value)
     * ===================================================== */

    public function getSetting(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val === false ? $default : (string) $val;
    }

    public function setSetting(string $key, string $value): void
    {
        $this->db()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        )->execute([$key, $value]);
    }

    /** Whether newly self-registered accounts must be approved by an admin. */
    public function requireApproval(): bool
    {
        return $this->getSetting('require_approval', '1') === '1';
    }
}
