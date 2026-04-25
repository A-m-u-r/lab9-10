<?php
declare(strict_types=1);

function allowed_slots(): array
{
    return ['09:00', '11:00', '13:00', '15:00'];
}

function slot_label(string $slot): string
{
    $end = (int)substr($slot, 0, 2) + 2;
    return $slot . '–' . sprintf('%02d:00', $end);
}

function taken_slots_for_master(int $masterId, string $date, ?int $excludeMcId = null): array
{
    $sql = 'SELECT time_slot FROM master_classes WHERE master_id = ? AND date = ?';
    $args = [$masterId, $date];
    if ($excludeMcId !== null) {
        $sql .= ' AND id <> ?';
        $args[] = $excludeMcId;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($args);
    return array_map(static fn(array $r): string => $r['time_slot'], $stmt->fetchAll());
}
