<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MasterClass;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $architecture = Category::create([
            'name' => 'Архитектурное моделирование',
            'description' => 'Изготовление моделей зданий, сооружений, транспорта и исторических памятников. Участники работают с простыми материалами, изучают основы композиции и создают собственные макеты.',
            'image' => 'cat-architecture.jpg',
        ]);

        $cooking = Category::create([
            'name' => 'Кулинария',
            'description' => 'Практические занятия по приготовлению блюд и сладостей: от шоколадных поделок до правильной прожарки стейков. Участники учатся выбирать продукты и готовить безопасно.',
            'image' => 'cat-cooking.jpg',
        ]);

        $wood = Category::create([
            'name' => 'Резьба по дереву',
            'description' => 'Знакомство с традиционной художественной обработкой дерева, базовыми инструментами и приемами создания декоративных изделий.',
            'image' => 'cat-wood.jpg',
        ]);

        $masters = collect([
            ['Иванова Ольга Ивановна', 'olga@example.com', '+79161234567', 'master-1.jpg'],
            ['Петров Сергей Петрович', 'sergey@example.com', '+79169876543', 'master-2.jpg'],
            ['Соколова Мария Андреевна', 'maria@example.com', '+79161112233', 'master-3.jpg'],
        ])->map(fn (array $master) => User::create([
            'full_name' => $master[0],
            'email' => $master[1],
            'phone' => $master[2],
            'password' => Hash::make('Master1234'),
            'role' => 'master',
            'photo' => $master[3],
        ]));

        User::create([
            'full_name' => 'Тестовый Посетитель Иванович',
            'email' => 'visitor@example.com',
            'phone' => '+79160000000',
            'password' => Hash::make('Visitor1234'),
            'role' => 'visitor',
            'photo' => null,
        ]);

        $base = CarbonImmutable::tomorrow();

        $items = [
            [$architecture, $masters[0], 'Моделирование транспорта', 'Основы моделирования судов, самолетов и автомобилей: участники строят, испытывают и запускают готовые модели.', 1, '09:00', 8, 1500],
            [$architecture, $masters[0], 'Моделирование зданий', 'Конструирование малоэтажных зданий: основания, стены, крыши и работа с бамбуком как основным элементом макета.', 2, '13:00', 6, 1800],
            [$cooking, $masters[1], 'Шоколадные поделки', 'Шоколадные фигуры, конфеты ручной работы и десерты из проверенных ингредиентов.', 3, '11:00', 10, 2000],
            [$cooking, $masters[1], 'Приготовление стейков', 'Подбор мяса, степени прожарки, гарнир и идеальный соус. Готовим вместе и пробуем результат.', 4, '15:00', 6, 2500],
            [$wood, $masters[2], 'Геометрическая резьба', 'Знакомство с основными элементами геометрической резьбы и создание узоров на деревянной заготовке.', 5, '09:00', 8, 1200],
            [$wood, $masters[2], 'Деревянные игрушки', 'Вырезание простых фигурок из дерева, безопасная обработка поверхности и финальная отделка изделия.', 6, '11:00', 8, 1400],
        ];

        foreach ($items as [$category, $master, $title, $description, $offset, $slot, $capacity, $price]) {
            MasterClass::create([
                'category_id' => $category->id,
                'master_id' => $master->id,
                'title' => $title,
                'description' => $description,
                'date' => $base->addDays($offset)->toDateString(),
                'time_slot' => $slot,
                'capacity' => $capacity,
                'price' => $price,
            ]);
        }
    }
}
