<?php
declare(strict_types=1);

function auth_register_form(): void
{
    if (current_user()) {
        redirect('home');
    }
    render('register', ['errors' => flash_get('errors', [])], 'Регистрация');
}

function auth_register(): void
{
    csrf_check();
    if (current_user()) {
        redirect('home');
    }

    $v = new Validator($_POST);
    $v->fullName('full_name', 'ФИО')
      ->email('email', 'Email')
      ->phone('phone', 'Номер телефона')
      ->password('password', 'Пароль')
      ->in('role', 'Роль', ['visitor', 'master']);

    if (!$v->fails() && ($v->clean['password'] ?? null) !== ($_POST['password_confirm'] ?? null)) {
        $v->errors['password_confirm'][] = 'Пароли не совпадают.';
    }

    if (!$v->fails()) {
        $stmt = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$v->clean['email']]);
        if ($stmt->fetch()) {
            $v->errors['email'][] = 'Пользователь с таким email уже зарегистрирован.';
        }
    }

    if ($v->fails()) {
        old_set($_POST);
        flash_set('errors', $v->errors);
        redirect('register');
    }

    $stmt = db()->prepare('
        INSERT INTO users (full_name, email, phone, password_hash, role, photo)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $v->clean['full_name'],
        $v->clean['email'],
        $v->clean['phone'],
        password_hash($v->clean['password'], PASSWORD_DEFAULT),
        $v->clean['role'],
        null,
    ]);
    login_user((int)db()->lastInsertId());
    flash_set('success', 'Регистрация прошла успешно. Добро пожаловать!');
    redirect('home');
}

function auth_login_form(): void
{
    if (current_user()) {
        redirect('home');
    }
    render('login', ['errors' => flash_get('errors', [])], 'Вход');
}

function auth_login(): void
{
    csrf_check();
    if (current_user()) {
        redirect('home');
    }

    $v = new Validator($_POST);
    $v->email('email', 'Email');
    if (!isset($_POST['password']) || $_POST['password'] === '') {
        $v->errors['password'][] = 'Введите пароль.';
    }

    if (!$v->fails()) {
        $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$v->clean['email']]);
        $row = $stmt->fetch();
        if (!$row || !password_verify((string)$_POST['password'], $row['password_hash'])) {
            $v->errors['email'][] = 'Неверный email или пароль.';
        } else {
            login_user((int)$row['id']);
            flash_set('success', 'Вы успешно вошли.');
            redirect('home');
        }
    }

    old_set(['email' => $_POST['email'] ?? '']);
    flash_set('errors', $v->errors);
    redirect('login');
}

function auth_logout(): void
{
    csrf_check();
    logout_user();
    redirect('home');
}
