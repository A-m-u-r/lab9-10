<?php
declare(strict_types=1);

function cabinet_index(): void
{
    $user = require_master();

    $stmt = db()->prepare('
        SELECT mc.*,
               c.name AS category_name,
               (SELECT COUNT(*) FROM bookings WHERE master_class_id = mc.id) AS booked
        FROM master_classes mc
        JOIN categories c ON c.id = mc.category_id
        WHERE mc.master_id = ?
        ORDER BY mc.date, mc.time_slot
    ');
    $stmt->execute([$user['id']]);
    $items = $stmt->fetchAll();

    render('cabinet', ['user' => $user, 'items' => $items, '__bodyClass' => 'dp'], 'Личный кабинет');
}
