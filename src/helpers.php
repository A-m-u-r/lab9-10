<?php
declare(strict_types=1);

function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $page, array $params = []): string
{
    $params = ['p' => $page] + $params;
    return 'index.php?' . http_build_query($params);
}

function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

function flash_set(string $key, $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_get(string $key, $default = null)
{
    if (!isset($_SESSION['_flash'][$key])) {
        return $default;
    }
    $v = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $v;
}

function flash_peek(string $key, $default = null)
{
    return $_SESSION['_flash'][$key] ?? $default;
}

function old_set(array $input): void
{
    $_SESSION['_old'] = $input;
}

function old(string $key, $default = ''): string
{
    return (string)($_SESSION['_old'][$key] ?? $default);
}

function old_clear(): void
{
    unset($_SESSION['_old']);
}

function render(string $view, array $data = [], ?string $title = null): void
{
    extract($data, EXTR_SKIP);
    $__title = $title ?? 'Очумелые ручки';
    require dirname(__DIR__) . '/views/partials/header.php';
    require dirname(__DIR__) . '/views/' . $view . '.php';
    require dirname(__DIR__) . '/views/partials/footer.php';
    old_clear();
}

function months_ru(): array
{
    return [
        1 => 'января',  2 => 'февраля',  3 => 'марта',
        4 => 'апреля',  5 => 'мая',      6 => 'июня',
        7 => 'июля',    8 => 'августа',  9 => 'сентября',
       10 => 'октября',11 => 'ноября',  12 => 'декабря',
    ];
}

function format_date_ru(string $isoDate): string
{
    [$y, $m, $d] = explode('-', $isoDate);
    return (int)$d . ' ' . months_ru()[(int)$m] . ' ' . $y;
}

function json_response($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
