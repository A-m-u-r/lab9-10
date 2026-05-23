<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterClassLabTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_category_pages_are_available(): void
    {
        $this->seed();

        $category = Category::firstOrFail();
        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();

        $this->get(route('home'))->assertOk();

        $this->actingAs($visitor)
            ->get(route('home'))
            ->assertOk();

        $this->get(route('categories.show', $category))
            ->assertOk()
            ->assertSee($category->name, false);
    }

    public function test_registration_login_and_logout_flow(): void
    {
        $this->post(route('register.store'), [
            'full_name' => 'Test Master',
            'email' => ' TEST.MASTER@example.com ',
            'phone' => '8 (916) 555-44-33',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'master',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test.master@example.com',
            'phone' => '+79165554433',
            'role' => 'master',
        ]);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $user = User::where('email', 'test.master@example.com')->firstOrFail();

        $this->post(route('login.store'), [
            'email' => ' TEST.MASTER@example.com ',
            'password' => 'Password1',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_and_login_validation_errors(): void
    {
        $this->post(route('register.store'), [
            'full_name' => 'Bad Phone',
            'email' => 'bad-phone@example.com',
            'phone' => '12345',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'visitor',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();

        User::create([
            'full_name' => 'Login User',
            'email' => 'login@example.com',
            'phone' => '+79160000001',
            'password' => Hash::make('Password1'),
            'role' => 'visitor',
            'photo' => null,
        ]);

        $this->post(route('login.store'), [
            'email' => 'login@example.com',
            'password' => 'WrongPassword1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_users_are_redirected_from_guest_forms(): void
    {
        $this->seed();

        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();

        $this->actingAs($visitor)
            ->get(route('register'))
            ->assertRedirect(route('home'));

        $this->actingAs($visitor)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    public function test_master_can_create_class_and_slots_api_returns_taken_slot(): void
    {
        $this->seed();

        $master = User::where('email', 'olga@example.com')->firstOrFail();
        $category = Category::firstOrFail();
        $date = now()->addDays(20)->toDateString();

        $this->actingAs($master)->post(route('master-classes.store'), [
            'category_id' => $category->id,
            'title' => 'Test master class',
            'description' => 'Detailed description for a generated test master class.',
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

    public function test_master_can_view_edit_and_update_own_class(): void
    {
        $this->seed();

        $masterClass = MasterClass::firstOrFail();
        $master = $masterClass->master;

        $this->actingAs($master)
            ->get(route('cabinet'))
            ->assertOk();

        $this->actingAs($master)
            ->get(route('master-classes.create'))
            ->assertOk();

        $this->actingAs($master)
            ->get(route('master-classes.show', $masterClass))
            ->assertOk();

        $this->actingAs($master)
            ->get(route('master-classes.edit', $masterClass))
            ->assertOk();

        $this->actingAs($master)->put(route('master-classes.update', $masterClass), [
            'description' => 'Updated description with enough symbols for validation.',
            'price' => 3333,
        ])->assertRedirect(route('cabinet'));

        $this->assertDatabaseHas('master_classes', [
            'id' => $masterClass->id,
            'description' => 'Updated description with enough symbols for validation.',
            'price' => 3333,
        ]);
    }

    public function test_non_master_and_foreign_master_cannot_manage_master_classes(): void
    {
        $this->seed();

        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();
        $foreignMasterClass = MasterClass::whereHas('master', fn ($query) => $query->where('email', 'sergey@example.com'))
            ->firstOrFail();
        $olga = User::where('email', 'olga@example.com')->firstOrFail();

        $this->actingAs($visitor)
            ->get(route('master-classes.create'))
            ->assertForbidden();

        $this->actingAs($olga)
            ->get(route('master-classes.show', $foreignMasterClass))
            ->assertForbidden();
    }

    public function test_master_cannot_reuse_own_slot(): void
    {
        $this->seed();

        $existing = MasterClass::firstOrFail();
        $master = $existing->master;

        $this->actingAs($master)->post(route('master-classes.store'), [
            'category_id' => $existing->category_id,
            'title' => 'Duplicate slot',
            'description' => 'Description long enough for validation.',
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

    public function test_booking_confirm_cancel_and_full_class_paths(): void
    {
        $this->seed();

        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();
        $masterClass = MasterClass::firstOrFail();

        $this->actingAs($visitor)
            ->get(route('bookings.confirm', $masterClass))
            ->assertOk();

        $this->actingAs($visitor)->post(route('bookings.store'), [
            'master_class_id' => $masterClass->id,
            'action' => 'cancel',
        ])->assertRedirect(route('categories.show', $masterClass->category_id))
            ->assertSessionHas('success');

        User::factory()
            ->count($masterClass->capacity)
            ->create()
            ->each(fn (User $user) => Booking::create([
                'user_id' => $user->id,
                'master_class_id' => $masterClass->id,
            ]));

        $this->actingAs($visitor)
            ->get(route('bookings.confirm', $masterClass))
            ->assertRedirect(route('categories.show', $masterClass->category_id))
            ->assertSessionHas('error');

        $this->actingAs($visitor)->post(route('bookings.store'), [
            'master_class_id' => $masterClass->id,
            'action' => 'confirm',
        ])->assertRedirect(route('categories.show', $masterClass->category_id))
            ->assertSessionHas('error');
    }

    public function test_visitor_can_cancel_own_booking_only(): void
    {
        $this->seed();

        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();
        $otherVisitor = User::factory()->create();
        $masterClass = MasterClass::firstOrFail();
        $booking = Booking::create([
            'user_id' => $visitor->id,
            'master_class_id' => $masterClass->id,
        ]);

        $this->actingAs($otherVisitor)
            ->delete(route('bookings.cancel', $booking))
            ->assertForbidden();

        $this->actingAs($visitor)
            ->delete(route('bookings.cancel', $booking))
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('bookings', [
            'id' => $booking->id,
        ]);
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

    public function test_slots_api_rejects_visitors_and_invalid_dates(): void
    {
        $this->seed();

        $visitor = User::where('email', 'visitor@example.com')->firstOrFail();
        $master = User::where('email', 'olga@example.com')->firstOrFail();

        $this->actingAs($visitor)
            ->getJson(route('api.slots', ['date' => now()->toDateString()]))
            ->assertForbidden()
            ->assertJsonPath('error', 'forbidden');

        $this->actingAs($master)
            ->getJson(route('api.slots', ['date' => 'not-a-date']))
            ->assertUnprocessable()
            ->assertJsonPath('error', 'invalid_date');
    }

    public function test_slots_api_can_exclude_current_master_class(): void
    {
        $this->seed();

        $masterClass = MasterClass::firstOrFail();
        $master = $masterClass->master;

        $this->actingAs($master)
            ->getJson(route('api.slots', [
                'date' => $masterClass->date->toDateString(),
                'exclude' => $masterClass->id,
            ]))
            ->assertOk()
            ->assertJsonPath('taken', []);
    }

    public function test_model_helpers_return_expected_values(): void
    {
        $this->seed();

        $masterClass = MasterClass::withCount('bookings')->firstOrFail();

        $this->assertSame('09:00-11:00', MasterClass::slotLabel('09:00'));
        $this->assertSame($masterClass->capacity, $masterClass->availablePlaces());
        $this->assertTrue($masterClass->master->isMaster());
        $this->assertCount(0, $masterClass->participants);
        $this->assertNotNull($masterClass->category->masterClasses()->first());
    }
}
