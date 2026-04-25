<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_name('mc_session');
session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/csrf.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/validator.php';
require __DIR__ . '/slots.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
