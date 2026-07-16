<?php
declare(strict_types=1);

/**
 * Front controller — all requests (except real files) route through here.
 */

// Routing health probe used by install.php to verify mod_rewrite / .htaccess.
// Handled first so it works even before config exists: if this responds with
// the token, the request was successfully rewritten to the front controller.
if (preg_match('#/__rewrite_check/?$#', parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'RVC_ROUTING_OK';
    exit;
}

session_start();

require __DIR__ . '/app/App.php';
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/Auth.php';
require __DIR__ . '/app/Repository.php';
require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/ResearchFormTrait.php';
require __DIR__ . '/app/controllers/PublicController.php';
require __DIR__ . '/app/controllers/AuthController.php';
require __DIR__ . '/app/controllers/AccountController.php';
require __DIR__ . '/app/controllers/MemberController.php';
require __DIR__ . '/app/controllers/AdminController.php';

App::boot();

// ---- resolve the route path relative to the app base ----
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$base = App::basePath();
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
$path   = trim(rawurldecode($uri), '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/*
 * Route table: [method, pattern, [Controller, action], access?]
 * access: absent = public, 'login' = any signed-in user, 'admin' = admin role.
 * {id} in a pattern captures a numeric segment.
 */
$routes = [
    ['GET',  '',                              [PublicController::class, 'home']],
    ['GET',  'search',                        [PublicController::class, 'search']],
    ['GET',  'research/{id}',                 [PublicController::class, 'detail']],
    ['GET',  'download/{id}',                 [PublicController::class, 'download']],

    ['GET',  'login',                         [AuthController::class, 'showLogin']],
    ['POST', 'login',                         [AuthController::class, 'login']],
    ['POST', 'logout',                        [AuthController::class, 'logout']],
    ['GET',  'register',                       [AuthController::class, 'showRegister']],
    ['POST', 'register',                       [AuthController::class, 'register']],

    // shared "my account" (any signed-in user)
    ['GET',  'account',                        [AccountController::class, 'show'],           'login'],
    ['POST', 'account/password',               [AccountController::class, 'changePassword'], 'login'],

    // member area (teachers/students) — own research only
    ['GET',  'my',                             [MemberController::class, 'dashboard'],       'login'],
    ['GET',  'my/submit',                       [MemberController::class, 'submitForm'],      'login'],
    ['POST', 'my/submit',                       [MemberController::class, 'submit'],          'login'],
    ['GET',  'my/research/{id}/edit',           [MemberController::class, 'submitForm'],      'login'],
    ['POST', 'my/research/{id}/edit',           [MemberController::class, 'submit'],          'login'],
    ['POST', 'my/research/{id}/delete',         [MemberController::class, 'deleteResearch'],  'login'],

    // admin area — admin role only
    ['GET',  'admin',                         [AdminController::class, 'dashboard'],       'admin'],
    ['GET',  'admin/research',                [AdminController::class, 'research'],         'admin'],
    ['POST', 'admin/research/{id}/approve',   [AdminController::class, 'approve'],          'admin'],
    ['POST', 'admin/research/{id}/status',    [AdminController::class, 'cycleStatus'],      'admin'],
    ['POST', 'admin/research/{id}/delete',    [AdminController::class, 'deleteResearch'],   'admin'],
    ['GET',  'admin/submit',                  [AdminController::class, 'submitForm'],       'admin'],
    ['POST', 'admin/submit',                  [AdminController::class, 'submit'],           'admin'],
    ['GET',  'admin/research/{id}/edit',      [AdminController::class, 'submitForm'],       'admin'],
    ['POST', 'admin/research/{id}/edit',      [AdminController::class, 'submit'],           'admin'],
    ['GET',  'admin/categories',             [AdminController::class, 'categories'],       'admin'],
    ['POST', 'admin/categories',             [AdminController::class, 'addCategory'],      'admin'],
    ['POST', 'admin/categories/{id}/rename', [AdminController::class, 'renameCategory'],   'admin'],
    ['POST', 'admin/categories/{id}/toggle', [AdminController::class, 'toggleCategory'],   'admin'],
    ['POST', 'admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'],   'admin'],
    ['GET',  'admin/users',                   [AdminController::class, 'users'],            'admin'],
    ['POST', 'admin/users',                   [AdminController::class, 'addUser'],          'admin'],
    ['POST', 'admin/users/settings',          [AdminController::class, 'updateSettings'],   'admin'],
    ['POST', 'admin/users/{id}/approve',      [AdminController::class, 'approveUser'],       'admin'],
    ['POST', 'admin/users/{id}/suspend',      [AdminController::class, 'suspendUser'],       'admin'],
];

foreach ($routes as $route) {
    [$rMethod, $pattern, $handler] = $route;
    $access = $route[3] ?? null;

    if ($rMethod !== $method) {
        continue;
    }
    $regex = '#^' . preg_replace('/\\\{id\\\}/', '(\d+)', preg_quote($pattern, '#')) . '$#';
    if (!preg_match($regex, $path, $m)) {
        continue;
    }

    if ($access === 'admin') {
        Auth::requireAdmin();
    } elseif ($access === 'login') {
        Auth::requireLogin();
    }

    $params = [];
    if (isset($m[1])) {
        $params['id'] = (int) $m[1];
    }

    [$class, $action] = $handler;
    (new $class())->$action($params);
    return;
}

// ---- no match ----
http_response_code(404);
App::render('public/not_found', [], [
    'section' => 'public', 'publicView' => 'home', 'title' => 'ไม่พบหน้าที่ต้องการ',
]);
