<?php
declare(strict_types=1);

function category_show(): void
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    if (!$category) {
        http_response_code(404);
        render('error', ['message' => 'Вид творчества не найден.'], 'Не найдено');
        return;
    }

    $stmt = db()->prepare('
        SELECT mc.*,
               u.full_name      AS master_name,
               u.photo          AS master_photo,
               (SELECT COUNT(*) FROM bookings WHERE master_class_id = mc.id) AS booked
        FROM master_classes mc
        JOIN users u ON u.id = mc.master_id
        WHERE mc.category_id = ? AND mc.date >= date("now")
        ORDER BY mc.date, mc.time_slot
    ');
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();

    $user = current_user();
    $userBookings = [];
    if ($user) {
        $stmt = db()->prepare('SELECT master_class_id FROM bookings WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $userBookings = array_column($stmt->fetchAll(), 'master_class_id');
    }

    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

    render('category', [
        'category'      => $category,
        'items'         => $items,
        'user'          => $user,
        'userBookings'  => $userBookings,
        'categories'    => $categories,
    ], $category['name']);
}
