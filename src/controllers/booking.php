<?php
declare(strict_types=1);

function booking_confirm_show(): void
{
    $user = require_auth();
    $id = isset($_GET['mc_id']) ? (int)$_GET['mc_id'] : 0;

    $mc = booking_load_mc($id);
    if ($user['role'] === 'master' && (int)$mc['master_id'] === (int)$user['id']) {
        flash_set('error', 'Вы не можете записаться на собственный мастер-класс.');
        redirect('category', ['id' => $mc['category_id']]);
    }

    $stmt = db()->prepare('SELECT 1 FROM bookings WHERE user_id = ? AND master_class_id = ?');
    $stmt->execute([$user['id'], $id]);
    if ($stmt->fetch()) {
        flash_set('error', 'Вы уже записаны на этот мастер-класс.');
        redirect('category', ['id' => $mc['category_id']]);
    }

    render('booking_confirm', [
        'user' => $user,
        'mc'   => $mc,
    ], 'Подтверждение записи');
}

function booking_create(): void
{
    csrf_check();
    $user = require_auth();
    $id = isset($_POST['mc_id']) ? (int)$_POST['mc_id'] : 0;
    $action = $_POST['action'] ?? '';

    $mc = booking_load_mc($id);

    if ($action === 'cancel') {
        flash_set('success', 'Запись не выполнена.');
        redirect('category', ['id' => $mc['category_id']]);
    }
    if ($action !== 'confirm') {
        flash_set('error', 'Неверное действие.');
        redirect('category', ['id' => $mc['category_id']]);
    }

    if ($user['role'] === 'master' && (int)$mc['master_id'] === (int)$user['id']) {
        flash_set('error', 'Вы не можете записаться на собственный мастер-класс.');
        redirect('category', ['id' => $mc['category_id']]);
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT capacity, (SELECT COUNT(*) FROM bookings WHERE master_class_id = ?) AS booked FROM master_classes WHERE id = ?');
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch();
        if (!$row) {
            $pdo->rollBack();
            http_response_code(404);
            render('error', ['message' => 'Мастер-класс не найден.'], 'Не найдено');
            return;
        }
        if ((int)$row['booked'] >= (int)$row['capacity']) {
            $pdo->rollBack();
            flash_set('error', 'Свободных мест больше нет.');
            redirect('category', ['id' => $mc['category_id']]);
        }

        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, master_class_id) VALUES (?, ?)');
        $stmt->execute([$user['id'], $id]);
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        flash_set('error', 'Вы уже записаны на этот мастер-класс.');
        redirect('category', ['id' => $mc['category_id']]);
    }

    flash_set('success', 'Запись подтверждена.');
    redirect('category', ['id' => $mc['category_id']]);
}

function booking_cancel(): void
{
    csrf_check();
    $user = require_auth();
    $bookingId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $stmt = db()->prepare('SELECT b.id, mc.category_id FROM bookings b JOIN master_classes mc ON mc.id = b.master_class_id WHERE b.id = ? AND b.user_id = ?');
    $stmt->execute([$bookingId, $user['id']]);
    $row = $stmt->fetch();
    if (!$row) {
        flash_set('error', 'Запись не найдена.');
        redirect('home');
    }

    $del = db()->prepare('DELETE FROM bookings WHERE id = ? AND user_id = ?');
    $del->execute([$bookingId, $user['id']]);

    flash_set('success', 'Запись отменена.');
    redirect('home');
}

function booking_load_mc(int $id): array
{
    $stmt = db()->prepare('
        SELECT mc.*, c.name AS category_name, u.full_name AS master_name
        FROM master_classes mc
        JOIN categories c ON c.id = mc.category_id
        JOIN users u      ON u.id = mc.master_id
        WHERE mc.id = ?
    ');
    $stmt->execute([$id]);
    $mc = $stmt->fetch();
    if (!$mc) {
        http_response_code(404);
        render('error', ['message' => 'Мастер-класс не найден.'], 'Не найдено');
        exit;
    }
    return $mc;
}

function api_slots(): void
{
    $user = current_user();
    if (!$user || $user['role'] !== 'master') {
        json_response(['error' => 'forbidden'], 403);
    }
    $date = $_GET['date'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        json_response(['error' => 'invalid_date'], 422);
    }
    $excludeId = isset($_GET['exclude']) ? (int)$_GET['exclude'] : null;
    $taken = taken_slots_for_master((int)$user['id'], $date, $excludeId);
    json_response(['taken' => $taken, 'allowed' => allowed_slots()]);
}
