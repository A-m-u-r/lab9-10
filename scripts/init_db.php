<?php
declare(strict_types=1);

require __DIR__ . '/../src/db.php';

$pdo = db();

$pdo->exec("DROP TABLE IF EXISTS bookings");
$pdo->exec("DROP TABLE IF EXISTS master_classes");
$pdo->exec("DROP TABLE IF EXISTS categories");
$pdo->exec("DROP TABLE IF EXISTS users");

$pdo->exec("
CREATE TABLE users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name     TEXT NOT NULL,
    email         TEXT NOT NULL UNIQUE COLLATE NOCASE,
    phone         TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    role          TEXT NOT NULL CHECK (role IN ('visitor','master')),
    photo         TEXT,
    created_at    TEXT NOT NULL DEFAULT (datetime('now'))
)");

$pdo->exec("
CREATE TABLE categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image       TEXT NOT NULL
)");

$pdo->exec("
CREATE TABLE master_classes (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL REFERENCES categories(id),
    master_id   INTEGER NOT NULL REFERENCES users(id),
    title       TEXT NOT NULL,
    description TEXT NOT NULL,
    date        TEXT NOT NULL,
    time_slot   TEXT NOT NULL CHECK (time_slot IN ('09:00','11:00','13:00','15:00')),
    capacity    INTEGER NOT NULL CHECK (capacity > 0),
    price       REAL NOT NULL CHECK (price >= 0),
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (master_id, date, time_slot)
)");

$pdo->exec("
CREATE TABLE bookings (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    master_class_id INTEGER NOT NULL REFERENCES master_classes(id) ON DELETE CASCADE,
    created_at      TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (user_id, master_class_id)
)");

$catStmt = $pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
$catStmt->execute([
    'Архитектурное моделирование',
    'Архитектурное моделирование — это изготовление моделей зданий, сооружений, исторических памятников, а также инженерных и фортификационных сооружений. Программа расширяет пространство для изучения народных традиций, даёт начальные навыки деревообработки и формирует эстетический вкус у учащихся.',
    'cat-architecture.jpg',
]);
$archId = (int)$pdo->lastInsertId();

$catStmt->execute([
    'Кулинария',
    'Кулинария — искусство приготовления пищи. Великие тайны кулинарии откроются перед теми, кто захочет научиться готовить по всем правилам, превращать сырые продукты во вкусную и полезную пищу. Программа стремится возродить традиции семейных праздников и здорового питания.',
    'cat-cooking.jpg',
]);
$cookId = (int)$pdo->lastInsertId();

$catStmt->execute([
    'Резьба по дереву',
    'Резьба по дереву — древнейший вид русского народного декоративного искусства. Программа знакомит учащихся с наследием художественной обработки дерева, прививает любовь к традиционному ремеслу и обучает практическим навыкам резьбы.',
    'cat-wood.jpg',
]);
$woodId = (int)$pdo->lastInsertId();

$userStmt = $pdo->prepare("
INSERT INTO users (full_name, email, phone, password_hash, role, photo)
VALUES (?, ?, ?, ?, ?, ?)
");

$masters = [
    ['Иванова Ольга Ивановна', 'olga@example.com',  '+79161234567', 'master-1.jpg'],
    ['Петров Сергей Петрович', 'sergey@example.com','+79169876543', 'master-2.jpg'],
    ['Соколова Мария Андреевна','maria@example.com', '+79161112233', 'master-3.jpg'],
];
$masterIds = [];
foreach ($masters as $m) {
    $userStmt->execute([$m[0], $m[1], $m[2], password_hash('Master1234', PASSWORD_DEFAULT), 'master', $m[3]]);
    $masterIds[] = (int)$pdo->lastInsertId();
}

$userStmt->execute(['Тестовый Посетитель Иванович', 'visitor@example.com', '+79160000000', password_hash('Visitor1234', PASSWORD_DEFAULT), 'visitor', null]);

$mcStmt = $pdo->prepare("
INSERT INTO master_classes (category_id, master_id, title, description, date, time_slot, capacity, price)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$base = new DateTimeImmutable('tomorrow');
$d = static fn(int $offset): string => $base->modify("+$offset days")->format('Y-m-d');

$mcStmt->execute([$archId, $masterIds[0], 'Моделирование транспорта', 'Основы моделирования различных видов транспорта: ученики строят, испытывают и запускают модели судов, самолётов и автомобилей.', $d(1), '09:00', 8, 1500.00]);
$mcStmt->execute([$archId, $masterIds[0], 'Моделирование зданий', 'Конструирование малоэтажных зданий: основания, стены, крыши; работа с бамбуком как основным элементом.', $d(2), '13:00', 6, 1800.00]);
$mcStmt->execute([$cookId, $masterIds[1], 'Шоколадные поделки', 'Шоколадные фонтаны, фруктовые пальмы, конфеты ручной работы и мороженое из проверенных компонентов.', $d(3), '11:00', 10, 2000.00]);
$mcStmt->execute([$cookId, $masterIds[1], 'Приготовление стейков', 'Подбор мяса, степени прожарки, гарнир и идеальный соус. Готовим вместе и пробуем.', $d(4), '15:00', 6, 2500.00]);
$mcStmt->execute([$woodId, $masterIds[2], 'Геометрическая резьба', 'Знакомство с основными элементами геометрической резьбы и созданием узоров на дереве.', $d(5), '09:00', 8, 1200.00]);
$mcStmt->execute([$woodId, $masterIds[2], 'Деревянные игрушки', 'Вырезание фигурок животных из качественных пород дерева; обработка натуральными составами.', $d(6), '11:00', 8, 1400.00]);

echo "Database initialized at " . dirname(__DIR__) . "/data/db.sqlite\n";
echo "Demo accounts:\n";
echo "  master:  olga@example.com / Master1234\n";
echo "  master:  sergey@example.com / Master1234\n";
echo "  master:  maria@example.com / Master1234\n";
echo "  visitor: visitor@example.com / Visitor1234\n";
