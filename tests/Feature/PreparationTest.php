<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PreparationTest extends TestCase
{
    use RefreshDatabase;

    private User $waiter;

    private Product $tea;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waiter = User::factory()->role(UserRole::Garson)->create();
        $this->tea = Product::factory()->create(['name' => 'Çay', 'price' => 15, 'stock_quantity' => 100]);
    }

    private function orderWithItem(): Order
    {
        $table = DiningTable::factory()->create(['number' => 4]);
        $this->actingAs($this->waiter)->post(route('tables.open-order', $table));
        $order = Order::firstOrFail();
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $this->tea->id, 'quantity' => 2]);

        return $order->fresh();
    }

    public function test_order_moves_through_preparation_steps(): void
    {
        $order = $this->orderWithItem();

        foreach ([OrderStatus::Hazirlaniyor, OrderStatus::Hazir, OrderStatus::TeslimEdildi] as $expected) {
            $before = $order->status;
            Carbon::setTestNow(now()->addMinutes(3));

            $this->actingAs($this->waiter)
                ->post(route('orders.advance', $order), ['from' => $before->value])
                ->assertSessionHas('success');

            $order->refresh();
            $this->assertSame($expected, $order->status);
            $this->assertSame(now()->toDateTimeString(), $order->status_changed_at->toDateTimeString());
        }

        // Teslim edilen sipariş akışın sonunda: ödeme alınana kadar açık kalır
        $this->actingAs($this->waiter)->post(route('orders.advance', $order))->assertSessionHas('error');
        $this->assertTrue($order->fresh()->isOpen());
        $this->assertSame(3, ActivityLog::where('action', 'siparis_durumu')->count());
    }

    public function test_empty_order_can_not_be_sent_to_preparation(): void
    {
        $this->actingAs($this->waiter)->post(route('orders.store'), ['type' => 'gel_al']);
        $order = Order::firstOrFail();

        $this->actingAs($this->waiter)
            ->postJson(route('orders.advance', $order), ['from' => 'yeni'])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertSame(OrderStatus::Yeni, $order->fresh()->status);
    }

    public function test_stale_status_from_another_screen_is_rejected(): void
    {
        $order = $this->orderWithItem();
        $this->actingAs($this->waiter)->post(route('orders.advance', $order), ['from' => 'yeni']);

        // Başka bir ekran hâlâ "yeni" görüyor: ikinci tıklama siparişi iki adım ilerletmemeli
        $this->actingAs($this->waiter)
            ->postJson(route('orders.advance', $order), ['from' => 'yeni'])
            ->assertStatus(422);

        $this->assertSame(OrderStatus::Hazirlaniyor, $order->fresh()->status);
    }

    public function test_adding_item_to_ready_order_sends_it_back_to_preparation_and_marks_late_items(): void
    {
        $order = $this->orderWithItem();
        $this->actingAs($this->waiter)->post(route('orders.advance', $order), ['from' => 'yeni']);
        $this->actingAs($this->waiter)->post(route('orders.advance', $order), ['from' => 'hazirlaniyor']);
        $this->assertSame(OrderStatus::Hazir, $order->fresh()->status);

        Carbon::setTestNow(now()->addMinutes(5));
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $this->tea->id, 'quantity' => 1]);

        $order = $order->fresh()->load('items');
        $this->assertSame(OrderStatus::Yeni, $order->status);
        $this->assertCount(1, $order->lateItemIds());
        $this->assertSame($order->items->last()->id, $order->lateItemIds()->first());

        $this->actingAs($this->waiter)->get(route('preparation.index'))
            ->assertOk()
            ->assertSee('Sonradan eklenen ürün var');
    }

    public function test_board_groups_orders_by_status_and_refreshes_as_json(): void
    {
        $order = $this->orderWithItem();
        $this->actingAs($this->waiter)->post(route('orders.advance', $order), ['from' => 'yeni']);

        $this->actingAs($this->waiter)
            ->getJson(route('preparation.index'))
            ->assertOk()
            ->assertJsonPath('counts.yeni', 0)
            ->assertJsonPath('counts.hazirlaniyor', 1)
            ->assertJsonPath('counts.hazir', 0)
            ->assertJsonStructure(['board', 'updatedAt']);

        $this->actingAs($this->waiter)->get(route('preparation.index'))
            ->assertOk()
            ->assertSee('Masa 4')
            ->assertSee('Hazır');
    }

    public function test_cashier_sees_board_but_can_not_change_status(): void
    {
        $order = $this->orderWithItem();
        $cashier = User::factory()->role(UserRole::Kasiyer)->create();

        $this->actingAs($cashier)->get(route('preparation.index'))
            ->assertOk()
            ->assertSee('Masa 4')
            ->assertDontSee('Hazırlamaya başla');

        $this->actingAs($cashier)->post(route('orders.advance', $order), ['from' => 'yeni'])->assertForbidden();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
