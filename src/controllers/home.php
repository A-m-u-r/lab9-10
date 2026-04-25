<?php
declare(strict_types=1);

function home_index(): void
{
    $categories = db()->query('SELECT id, name, image FROM categories ORDER BY name')->fetchAll();

    $myBookings = [];
    $user = current_user();
    if ($user) {
        $stmt = db()->prepare('
            SELECT mc.id, mc.title, mc.date, mc.time_slot, c.name AS category_name, u.full_name AS master_name
            FROM bookings b
            JOIN master_classes mc ON mc.id = b.master_class_id
            JOIN categories c      ON c.id = mc.category_id
            JOIN users u           ON u.id = mc.master_id
            WHERE b.user_id = ?
            ORDER BY mc.date, mc.time_slot
        ');
        $stmt->execute([$user['id']]);
        $myBookings = $stmt->fetchAll();
    }

    render('home', [
        'categories' => $categories,
        'myBookings' => $myBookings,
        'user'       => $user,
    ], 'Очумелые ручки');
}
