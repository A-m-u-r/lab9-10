<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

foreach (glob(__DIR__ . '/../src/controllers/*.php') as $__f) {
    require_once $__f;
}

$page   = (string)($_GET['p'] ?? 'home');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    'home'            => ['GET'  => 'home_index'],
    'category'        => ['GET'  => 'category_show'],
    'register'        => ['GET'  => 'auth_register_form', 'POST' => 'auth_register'],
    'login'           => ['GET'  => 'auth_login_form',    'POST' => 'auth_login'],
    'logout'          => ['POST' => 'auth_logout'],
    'cabinet'         => ['GET'  => 'cabinet_index'],
    'mc_new'          => ['GET'  => 'mc_create_form',     'POST' => 'mc_create'],
    'mc_edit'         => ['GET'  => 'mc_edit_form',       'POST' => 'mc_edit'],
    'mc_view'         => ['GET'  => 'mc_view'],
    'booking_confirm' => ['GET'  => 'booking_confirm_show'],
    'booking_create'  => ['POST' => 'booking_create'],
    'booking_cancel'  => ['POST' => 'booking_cancel'],
    'api_slots'       => ['GET'  => 'api_slots'],
];

if (!isset($routes[$page])) {
    http_response_code(404);
    render('error', ['message' => 'Страница не найдена.'], 'Не найдено');
    exit;
}
if (!isset($routes[$page][$method])) {
    http_response_code(405);
    header('Allow: ' . implode(', ', array_keys($routes[$page])));
    render('error', ['message' => 'Метод не поддерживается.'], 'Ошибка');
    exit;
}

$handler = $routes[$page][$method];
if (!function_exists($handler)) {
    http_response_code(500);
    render('error', ['message' => "Обработчик «{$handler}» не найден."], 'Ошибка');
    exit;
}
$handler();
