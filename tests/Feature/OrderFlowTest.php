<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\StockMovementType;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $waiter;

    private DiningTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waiter = User::factory()->role(UserRole::Garson)->create();
        $this->table = DiningTable::factory()->create(['number' => 4]);
    }

    private function openTableOrder(): Order
    {
        $this->actingAs($this->waiter)->post(route('tables.open-order', $this->table));

        return Order::firstOrFail();
    }

    public function test_waiter_opens_order_for_table_and_table_becomes_occupied(): void
    {
        $response = $this->actingAs($this->waiter)->post(route('orders.store'), [
            'type' => OrderType::Masa->value,
            'dining_table_id' => $this->table->id,
            'note' => 'Tost soğansız olsun',
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order));

        $this->assertSame(OrderStatus::Yeni, $order->status);
        $this->assertSame('Tost soğansız olsun', $order->note);
        $this->assertSame($this->waiter->id, $order->user_id);
        $this->assertSame(TableStatus::Dolu, $this->table->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => 'siparis_acildi', 'subject_id' => $order->id]);
    }

    public function test_opening_an_occupied_table_returns_its_existing_order(): void
    {
        $order = $this->openTableOrder();

        $this->actingAs($this->waiter)->post(route('tables.open-order', $this->table))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame(1, Order::count());
    }

    public function test_table_order_requires_a_table_and_takeaway_orders_do_not(): void
    {
        $this->actingAs($this->waiter)->post(route('orders.store'), ['type' => OrderType::Masa->value])
            ->assertSessionHasErrors('dining_table_id');

        $this->actingAs($this->waiter)->post(route('orders.store'), ['type' => OrderType::Paket->value])
            ->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(OrderType::Paket, $order->type);
        $this->assertNull($order->dining_table_id);
        $this->assertSame("Paket {$order->code()}", $order->label());
    }

    public function test_cashier_can_view_orders_but_cannot_take_them(): void
    {
        $order = $this->openTableOrder();
        $cashier = User::factory()->role(UserRole::Kasiyer)->create();

        $this->actingAs($cashier)->get(route('orders.index'))->assertOk()->assertSee($order->label());
        $this->actingAs($cashier)->get(route('orders.show', $order))->assertOk()->assertDontSee('Eklenecek adet');
        $this->actingAs($cashier)->post(route('orders.store'), ['type' => OrderType::Paket->value])->assertForbidden();
        $this->actingAs($cashier)->post(route('orders.items.store', $order), ['product_id' => 1])->assertForbidden();
    }

    public function test_adding_item_copies_price_decreases_stock_and_updates_total(): void
    {
        $order = $this->openTableOrder();
        $tea = Product::factory()->create(['name' => 'Çay', 'price' => 15, 'stock_quantity' => 50]);
        $toast = Product::factory()->create(['name' => 'Tost', 'price' => 80, 'stock_quantity' => 10]);

        $this->actingAs($this->waiter)
            ->postJson(route('orders.items.store', $order), ['product_id' => $tea->id, 'quantity' => 2])
            ->assertOk()
            ->assertJson(['ok' => true, 'message' => '2 × Çay eklendi.'])
            ->assertJsonPath('stock.'.$tea->id, 48);

        $this->actingAs($this->waiter)
            ->post(route('orders.items.store', $order), ['product_id' => $toast->id, 'quantity' => 1])
            ->assertSessionHas('success');

        $this->assertEquals(110, $order->fresh()->total);
        $this->assertSame(48, $tea->fresh()->stock_quantity);
        $this->assertSame(9, $toast->fresh()->stock_quantity);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_id' => $tea->id, 'unit_price' => 15, 'line_total' => 30]);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $tea->id, 'type' => StockMovementType::Satis->value, 'quantity' => -2]);
    }

    public function test_item_is_not_added_when_stock_is_insufficient_or_sale_is_stopped(): void
    {
        $order = $this->openTableOrder();
        $last = Product::factory()->create(['name' => 'Sufle', 'stock_quantity' => 1]);
        $stopped = Product::factory()->create(['name' => 'Eski Ürün', 'is_active' => false]);

        $this->actingAs($this->waiter)
            ->postJson(route('orders.items.store', $order), ['product_id' => $last->id, 'quantity' => 2])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->actingAs($this->waiter)
            ->postJson(route('orders.items.store', $order), ['product_id' => $stopped->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Eski Ürün ürününün satışı durdurulmuş.');

        $this->assertSame(0, $order->items()->count());
        $this->assertSame(1, $last->fresh()->stock_quantity);
    }

    public function test_cancelling_item_restores_stock_and_keeps_history(): void
    {
        $order = $this->openTableOrder();
        $tea = Product::factory()->create(['price' => 15, 'stock_quantity' => 10]);
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $tea->id, 'quantity' => 3]);
        $item = $order->items()->firstOrFail();

        $this->actingAs($this->waiter)
            ->post(route('orders.items.cancel', [$order, $item]), ['reason' => 'Yanlış girildi'])
            ->assertSessionHas('success');

        $item->refresh();
        $this->assertSame(OrderItemStatus::Iptal, $item->status);
        $this->assertSame('Yanlış girildi', $item->cancel_reason);
        $this->assertSame($this->waiter->id, $item->cancelled_by);
        $this->assertSame(10, $tea->fresh()->stock_quantity);
        $this->assertEquals(0, $order->fresh()->total);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $tea->id, 'type' => StockMovementType::Iade->value, 'quantity' => 3]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'kalem_iptal', 'subject_id' => $order->id]);
    }

    public function test_cancel_reason_is_required(): void
    {
        $order = $this->openTableOrder();
        $tea = Product::factory()->create();
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $tea->id]);

        $this->actingAs($this->waiter)
            ->post(route('orders.items.cancel', [$order, $order->items()->first()]), ['reason' => ''])
            ->assertSessionHasErrors('reason');
    }

    public function test_cancelling_order_frees_table_and_restores_all_stock(): void
    {
        $order = $this->openTableOrder();
        $tea = Product::factory()->create(['stock_quantity' => 20]);
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $tea->id, 'quantity' => 4]);

        $this->actingAs($this->waiter)
            ->post(route('orders.cancel', $order), ['reason' => 'Müşteri ayrıldı'])
            ->assertRedirect(route('orders.index'));

        $this->assertSame(OrderStatus::Iptal, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->closed_at);
        $this->assertSame(TableStatus::Bos, $this->table->fresh()->status);
        $this->assertSame(20, $tea->fresh()->stock_quantity);

        // Kapanan siparişe ürün eklenemez
        $this->actingAs($this->waiter)
            ->post(route('orders.items.store', $order), ['product_id' => $tea->id])
            ->assertSessionHas('error');
    }

    public function test_order_screens_render(): void
    {
        $order = $this->openTableOrder();
        Product::factory()->create(['name' => 'Latte']);

        $this->actingAs($this->waiter)->get(route('orders.show', $order))
            ->assertOk()->assertSee('Masa 4')->assertSee('Latte')->assertSee('Adisyon boş');
        $this->actingAs($this->waiter)->get(route('orders.index'))
            ->assertOk()->assertSee('Masa 4');
        $this->actingAs($this->waiter)->get(route('orders.index', ['gorunum' => 'gecmis']))
            ->assertOk()->assertSee('Kayıt bulunamadı');
        $this->actingAs($this->waiter)->get(route('tables.index'))
            ->assertOk()->assertSee('Siparişe Git');
    }
}
