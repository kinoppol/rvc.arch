<?php
declare(strict_types=1);

/**
 * Front controller — all requests (except real files) route through here.
 */

session_start();

require __DIR__ . '/app/App.php';
require __DIR__ . '/app/Database.php';
require __DIR__ . '/app/Auth.php';
require __DIR__ . '/app/Repository.php';
require __DIR__ . '/app/helpers.php';
require __DIR__ . '/app/controllers/PublicController.php';
require __DIR__ . '/app/controllers/AuthController.php';
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
 * Route table: [method, pattern, [Controller, action], adminOnly?]
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

    ['GET',  'admin',                         [AdminController::class, 'dashboard'],       true],
    ['GET',  'admin/research',                [AdminController::class, 'research'],         true],
    ['POST', 'admin/research/{id}/approve',   [AdminController::class, 'approve'],          true],
    ['POST', 'admin/research/{id}/status',    [AdminController::class, 'cycleStatus'],      true],
    ['POST', 'admin/research/{id}/delete',    [AdminController::class, 'deleteResearch'],   true],
    ['GET',  'admin/submit',                  [AdminController::class, 'submitForm'],       true],
    ['POST', 'admin/submit',                  [AdminController::class, 'submit'],           true],
    ['GET',  'admin/research/{id}/edit',      [AdminController::class, 'submitForm'],       true],
    ['POST', 'admin/research/{id}/edit',      [AdminController::class, 'submit'],           true],
    ['GET',  'admin/categories',             [AdminController::class, 'categories'],       true],
    ['POST', 'admin/categories',             [AdminController::class, 'addCategory'],      true],
    ['POST', 'admin/categories/{id}/rename', [AdminController::class, 'renameCategory'],   true],
    ['POST', 'admin/categories/{id}/toggle', [AdminController::class, 'toggleCategory'],   true],
    ['POST', 'admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'],   true],
    ['GET',  'admin/users',                   [AdminController::class, 'users'],            true],
    ['POST', 'admin/users',                   [AdminController::class, 'addUser'],          true],
    ['POST', 'admin/users/settings',          [AdminController::class, 'updateSettings'],   true],
    ['POST', 'admin/users/{id}/approve',      [AdminController::class, 'approveUser'],       true],
    ['POST', 'admin/users/{id}/suspend',      [AdminController::class, 'suspendUser'],       true],
];

foreach ($routes as $route) {
    [$rMethod, $pattern, $handler] = $route;
    $adminOnly = $route[3] ?? false;

    if ($rMethod !== $method) {
        continue;
    }
    $regex = '#^' . preg_replace('/\\\{id\\\}/', '(\d+)', preg_quote($pattern, '#')) . '$#';
    if (!preg_match($regex, $path, $m)) {
        continue;
    }

    if ($adminOnly) {
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
