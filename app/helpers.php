<?php
declare(strict_types=1);

/**
 * Global helper functions used across views and controllers.
 */

/** HTML-escape. */
function h(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** App URL from route path. */
function url(string $path = ''): string
{
    return App::url($path);
}

/** Static asset URL. */
function asset(string $path): string
{
    return App::asset($path);
}

/** Redirect to an app route and stop. */
function redirect(string $path = ''): never
{
    header('Location: ' . App::url($path));
    exit;
}

/** Redirect back to the referring page (or a fallback). */
function redirect_back(string $fallback = ''): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref !== '') {
        header('Location: ' . $ref);
        exit;
    }
    redirect($fallback);
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['_csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('คำขอไม่ถูกต้อง (CSRF token mismatch) — กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง');
    }
}

/* ---------- Flash messages ---------- */

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['_flash'][] = ['message' => $message, 'type' => $type];
}

/** @return array<int,array{message:string,type:string}> */
function take_flash(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/* ---------- Misc view helpers ---------- */

/** Two-character initial from a Thai/English name (drops the title prefix). */
function name_initial(?string $name): string
{
    $t = preg_replace('/^(นาย|นางสาว|นาง|เด็กชาย|เด็กหญิง)/u', '', trim((string) $name));
    return mb_substr($t, 0, 2, 'UTF-8') ?: '?';
}

/** Category accent colour by its position among all categories. */
function category_color(int $index): string
{
    $colors = App::CAT_COLORS;
    return $colors[$index % count($colors)];
}

/** [bg, fg] CSS vars for a research status badge. */
function status_meta(string $status): array
{
    return match ($status) {
        'เผยแพร่'     => ['var(--ok-soft)', 'var(--ok)'],
        'รอตรวจสอบ'  => ['var(--warn-soft)', 'var(--warn)'],
        'ไม่เผยแพร่'  => ['var(--danger-soft)', 'var(--danger)'],
        default        => ['var(--surface-3)', 'var(--muted)'], // แบบร่าง
    };
}

/** [label, bg, fg] for a user account-status badge. */
function user_status_meta(string $status): array
{
    return match ($status) {
        'approved'  => ['ใช้งาน', 'var(--ok-soft)', 'var(--ok)'],
        'pending'   => ['รอการอนุมัติ', 'var(--warn-soft)', 'var(--warn)'],
        'suspended' => ['ระงับ', 'var(--danger-soft)', 'var(--danger)'],
        default      => ['—', 'var(--surface-3)', 'var(--muted)'],
    };
}

/** [bg, fg] CSS vars for a user role badge. */
function role_meta(string $role): array
{
    return match ($role) {
        'ผู้ดูแลระบบ'      => ['var(--primary-soft)', 'var(--primary-text)'],
        'ครู'              => ['var(--info-soft)', 'var(--info)'],
        'ตัวแทนนักศึกษา'  => ['var(--ok-soft)', 'var(--ok)'],
        default             => ['var(--surface-3)', 'var(--muted)'],
    };
}

/** Human-readable file size. */
function human_size(?int $bytes): string
{
    if (!$bytes) {
        return '—';
    }
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes, 1024));
    $i = max(0, min($i, count($units) - 1));
    return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
}

/** Shorten a Thai string to $len characters with an ellipsis. */
function excerpt(?string $s, int $len = 90): string
{
    $s = trim((string) $s);
    return mb_strlen($s, 'UTF-8') > $len ? mb_substr($s, 0, $len, 'UTF-8') . '…' : $s;
}
