<?php
declare(strict_types=1);

function current_user(): ?array
{
    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }
    $cached = true;

    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        return $user;
    }
    $stmt = db()->prepare('SELECT id, full_name, email, phone, role, photo FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $user = $row ?: null;
    if ($user === null) {
        unset($_SESSION['user_id']);
    }
    return $user;
}

function require_auth(): array
{
    $u = current_user();
    if (!$u) {
        flash_set('next', $_SERVER['REQUEST_URI'] ?? 'index.php');
        flash_set('error', 'Для продолжения войдите в систему.');
        redirect('login');
    }
    return $u;
}

function require_master(): array
{
    $u = require_auth();
    if ($u['role'] !== 'master') {
        http_response_code(403);
        render('error', ['message' => 'Доступ только для ведущих мастер-классов.'], 'Доступ запрещён');
        exit;
    }
    return $u;
}

function login_user(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
