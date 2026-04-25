<?php
declare(strict_types=1);

function mc_create_form(): void
{
    $user = require_master();
    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    render('mc_form', [
        'user'       => $user,
        'categories' => $categories,
        'errors'     => flash_get('errors', []),
        'mode'       => 'create',
        'mc'         => null,
        'allowedSlots' => allowed_slots(),
    ], 'Новый мастер-класс');
}

function mc_create(): void
{
    csrf_check();
    $user = require_master();

    $v = new Validator($_POST);
    $v->intRange('category_id', 'Вид творчества', 1, PHP_INT_MAX)
      ->string('title', 'Название', 3, 120)
      ->string('description', 'Описание', 10, 2000)
      ->dateNotPast('date', 'Дата')
      ->in('time_slot', 'Время', allowed_slots())
      ->intRange('capacity', 'Количество человек в группе', 1, 100)
      ->priceRange('price', 'Стоимость', 0, 1000000);

    if (!$v->fails()) {
        $stmt = db()->prepare('SELECT 1 FROM categories WHERE id = ?');
        $stmt->execute([$v->clean['category_id']]);
        if (!$stmt->fetch()) {
            $v->errors['category_id'][] = 'Выбранный вид творчества не существует.';
        }
    }

    if (!$v->fails()) {
        $taken = taken_slots_for_master($user['id'], $v->clean['date']);
        if (in_array($v->clean['time_slot'], $taken, true)) {
            $v->errors['time_slot'][] = 'На эту дату и время у вас уже запланирован мастер-класс.';
        }
    }

    if ($v->fails()) {
        old_set($_POST);
        flash_set('errors', $v->errors);
        redirect('mc_new');
    }

    try {
        $stmt = db()->prepare('
            INSERT INTO master_classes (category_id, master_id, title, description, date, time_slot, capacity, price)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $v->clean['category_id'],
            $user['id'],
            $v->clean['title'],
            $v->clean['description'],
            $v->clean['date'],
            $v->clean['time_slot'],
            $v->clean['capacity'],
            $v->clean['price'],
        ]);
    } catch (PDOException $e) {
        old_set($_POST);
        flash_set('errors', ['time_slot' => ['Это время уже занято — выберите другое.']]);
        redirect('mc_new');
    }

    flash_set('success', 'Мастер-класс создан.');
    redirect('cabinet');
}

function mc_edit_form(): void
{
    $user = require_master();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $mc = mc_load_owned($id, (int)$user['id']);

    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

    render('mc_form', [
        'user'         => $user,
        'categories'   => $categories,
        'errors'       => flash_get('errors', []),
        'mode'         => 'edit',
        'mc'           => $mc,
        'allowedSlots' => allowed_slots(),
    ], 'Редактирование мастер-класса');
}

function mc_edit(): void
{
    csrf_check();
    $user = require_master();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $mc = mc_load_owned($id, (int)$user['id']);

    $v = new Validator($_POST);
    $v->string('description', 'Описание', 10, 2000)
      ->priceRange('price', 'Стоимость', 0, 1000000);

    if ($v->fails()) {
        old_set($_POST);
        flash_set('errors', $v->errors);
        redirect('mc_edit', ['id' => $id]);
    }

    $stmt = db()->prepare('UPDATE master_classes SET description = ?, price = ? WHERE id = ? AND master_id = ?');
    $stmt->execute([$v->clean['description'], $v->clean['price'], $id, $user['id']]);

    flash_set('success', 'Изменения сохранены.');
    redirect('cabinet');
}

function mc_view(): void
{
    $user = require_master();
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $mc = mc_load_owned($id, (int)$user['id']);

    $stmt = db()->prepare('SELECT name FROM categories WHERE id = ?');
    $stmt->execute([$mc['category_id']]);
    $categoryName = (string)$stmt->fetchColumn();

    $stmt = db()->prepare('
        SELECT u.full_name, u.email, u.phone, b.created_at
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        WHERE b.master_class_id = ?
        ORDER BY b.created_at
    ');
    $stmt->execute([$id]);
    $participants = $stmt->fetchAll();

    render('mc_view', [
        'user'         => $user,
        'mc'           => $mc,
        'categoryName' => $categoryName,
        'participants' => $participants,
    ], 'Мастер-класс: ' . $mc['title']);
}

function mc_load_owned(int $id, int $masterId): array
{
    $stmt = db()->prepare('SELECT * FROM master_classes WHERE id = ?');
    $stmt->execute([$id]);
    $mc = $stmt->fetch();
    if (!$mc) {
        http_response_code(404);
        render('error', ['message' => 'Мастер-класс не найден.'], 'Не найдено');
        exit;
    }
    if ((int)$mc['master_id'] !== $masterId) {
        http_response_code(403);
        render('error', ['message' => 'Это не ваш мастер-класс.'], 'Доступ запрещён');
        exit;
    }
    return $mc;
}
