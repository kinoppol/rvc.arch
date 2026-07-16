<?php
declare(strict_types=1);

/**
 * Application container: config, constants, base-path detection, view rendering.
 */
final class App
{
    private static array $config = [];

    /** Category accent colours — mirror --c1..--c5 from the design. */
    public const CAT_COLORS = ['var(--c1)', 'var(--c2)', 'var(--c3)', 'var(--c4)', 'var(--c5)'];

    /** Fixed chapter slots for per-chapter PDF uploads. */
    public const CHAPTER_NAMES = [
        'หน้าปก',
        'บทคัดย่อ',
        'บทที่ 1 บทนำ',
        'บทที่ 2 เอกสารที่เกี่ยวข้อง',
        'บทที่ 3 วิธีดำเนินการ',
        'บทที่ 4 ผลการวิเคราะห์',
        'บทที่ 5 สรุปและอภิปราย',
        'บรรณานุกรม',
        'ภาคผนวก',
    ];

    /** Departments (สาขาวิชา/แผนกวิชา) used in dropdowns. */
    public const DEPTS = [
        'การบัญชี', 'การตลาด', 'คอมพิวเตอร์ธุรกิจ', 'การโรงแรม',
        'อาหารและโภชนาการ', 'เทคโนโลยีสารสนเทศ', 'คหกรรมศาสตร์',
    ];

    public const STATUSES = ['เผยแพร่', 'รอตรวจสอบ', 'แบบร่าง', 'ไม่เผยแพร่'];
    public const AUTHOR_ROLES = ['ผู้วิจัยหลัก', 'ผู้วิจัยร่วม', 'ครูที่ปรึกษา'];

    public static function boot(): void
    {
        $path = dirname(__DIR__) . '/config/config.php';
        if (!is_file($path)) {
            http_response_code(500);
            exit('Missing config/config.php — copy config/config.sample.php to config/config.php.');
        }
        self::$config = require $path;
    }

    /** @return mixed */
    public static function config(string $section)
    {
        return self::$config[$section] ?? null;
    }

    /** Base URL path the app is mounted at, e.g. "/rvc.arch". */
    public static function basePath(): string
    {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return rtrim($dir, '/');
    }

    /** Build an app URL from a route path. */
    public static function url(string $path = ''): string
    {
        return self::basePath() . '/' . ltrim($path, '/');
    }

    /** URL for a static asset under /assets, /storage handled separately. */
    public static function asset(string $path): string
    {
        return self::basePath() . '/assets/' . ltrim($path, '/');
    }

    /**
     * Render a view file inside the base layout and echo it.
     *
     * @param array<string,mixed> $vars   variables for the view
     * @param array<string,mixed> $layout variables for the layout (title, section...)
     */
    public static function render(string $view, array $vars = [], array $layout = []): void
    {
        $content = self::partial($view, $vars);
        $layout['content'] = $content;
        echo self::partial('layout', $layout);
    }

    /** Render a view file to a string. */
    public static function partial(string $view, array $vars = []): string
    {
        $file = dirname(__DIR__) . '/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$view}");
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
