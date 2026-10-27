<?php

namespace Tests\Feature;

use App\Enums\OrderType;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiningTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_staff_can_see_table_statuses(): void
    {
        DiningTable::factory()->create(['number' => 4]);

        foreach (UserRole::cases() as $role) {
            $this->actingAs(User::factory()->role($role)->create())
                ->get(route('tables.index'))
                ->assertOk()
                ->assertSee('Masa 4');
        }
    }

    public function test_only_managers_can_create_tables(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();
        $manager = User::factory()->role(UserRole::Yonetici)->create();

        $this->actingAs($waiter)->get(route('tables.create'))->assertForbidden();
        $this->actingAs($waiter)->post(route('tables.store'), ['number' => 1, 'capacity' => 4])->assertForbidden();

        $this->actingAs($manager)->get(route('tables.create'))->assertOk();
        $this->actingAs($manager)->post(route('tables.store'), [
            'number' => 7,
            'area' => 'Bahçe',
            'capacity' => 6,
            'is_active' => 1,
        ])->assertRedirect(route('tables.index'));

        $this->assertDatabaseHas('dining_tables', ['number' => 7, 'status' => TableStatus::Bos->value]);
    }

    public function test_table_numbers_must_be_unique(): void
    {
        DiningTable::factory()->create(['number' => 3]);

        $this->actingAs(User::factory()->role(UserRole::Yonetici)->create())
            ->post(route('tables.store'), ['number' => 3, 'capacity' => 4])
            ->assertSessionHasErrors('number');
    }

    public function test_occupied_table_shows_open_order_and_can_not_be_deactivated(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();
        $table = DiningTable::factory()->create(['number' => 4, 'status' => TableStatus::Dolu]);

        Order::create([
            'type' => OrderType::Masa,
            'dining_table_id' => $table->id,
            'user_id' => $manager->id,
            'total' => 110,
            'opened_at' => now(),
        ]);

        $this->actingAs($manager)->get(route('tables.index'))->assertSee('110,00 ₺');

        $this->actingAs($manager)->put(route('tables.update', $table), [
            'number' => 4,
            'capacity' => 4,
            'is_active' => 0,
        ])->assertSessionHas('error');

        $this->assertTrue($table->fresh()->is_active);
    }

    public function test_table_with_order_history_can_not_be_deleted(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();
        $table = DiningTable::factory()->create();

        Order::create([
            'type' => OrderType::Masa,
            'dining_table_id' => $table->id,
            'user_id' => $manager->id,
            'opened_at' => now(),
        ]);

        $this->actingAs($manager)->delete(route('tables.destroy', $table))->assertSessionHas('error');

        $this->assertModelExists($table);
    }
}
