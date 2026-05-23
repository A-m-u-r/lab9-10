<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterClassLabTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_category_pages_are_available(): void
    {
        $this->seed();

        $category = Category::firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ОчУмелые ручки', false);

        $this->get(route('categories.show', $category))
            ->assertOk()
            ->assertSee('Расписание', false)
            ->assertSee($category->name, false);
    }

    public function test_master_can_create_class_and_slots_api_returns_taken_slot(): void
    {
        $this->seed();

        $master = User::where('email', 'olga@example.com')->firstOrFail();
        $category = Category::firstOrFail();
        $date = now()->addDays(20)->toDateString();

        $this->actingAs($master)->post(route('master-classes.store'), [
            'category_id' => $category->id,
            'title' => 'Тестовый мастер-класс',
            'description' => 'Подробное описание тестового мастер-класса.',
            'date' => $date,
            'time_slot' => '09:00',
            'capacity' => 7,
            'price' => 900,
        ])->assertRedirect(route('cabinet'));

        $this->assertDatabaseHas('master_classes', [
            'master_id' => $master->id,
            'date' => $date,
            'time_slot' => '09:00',
        ]);

        $this->actingAs($master)
            ->getJson(route('api.slots', ['date' => $date]))
            ->assertOk()
            ->assertJsonPath('taken.0', '09:00');
    }

    public function test_master_cannot_reuse_own_slot(): void
    {
        $this->seed();

        $existing = MasterClass::firstOrFail();
        $master = $existing->master;

        $this->actingAs($master)->post(route('master-classes.store'), [
            'category_id' => $existing->category_id,
            'title' => 'Дубликат слота',
            'description' => 'Описание достаточно длинное для проверки.',
            'date' => $existing->date->toDateString(),
            'time_slot' => $existing->time_slot,
            'capacity' => 5,
            'price' => 1000,
        ])->assertSessionHasErrors('time_slot');
    }

    public function test_visitor_can_book_and_cannot_book_twice(): void
    {
        $this->seed();

        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();
        $masterClass = MasterClass::firstOrFail();

        $this->actingAs($visitor)->post(route('bookings.store'), [
            'master_class_id' => $masterClass->id,
            'action' => 'confirm',
        ])->assertRedirect(route('categories.show', $masterClass->category_id));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $visitor->id,
            'master_class_id' => $masterClass->id,
        ]);

        $this->actingAs($visitor)->post(route('bookings.store'), [
            'master_class_id' => $masterClass->id,
            'action' => 'confirm',
        ])->assertSessionHas('error');
    }

    public function test_master_cannot_book_own_class(): void
    {
        $this->seed();

        $masterClass = MasterClass::firstOrFail();

        $this->actingAs($masterClass->master)
            ->get(route('bookings.confirm', $masterClass))
            ->assertRedirect(route('categories.show', $masterClass->category_id))
            ->assertSessionHas('error');
    }
}
