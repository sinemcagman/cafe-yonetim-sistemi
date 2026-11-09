<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $waiter;

    private Product $tea;

    private Product $toast;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waiter = User::factory()->role(UserRole::Garson)->create();
        $this->tea = Product::factory()->create(['name' => 'Çay', 'price' => 15, 'stock_quantity' => 100]);
        $this->toast = Product::factory()->create(['name' => 'Tost', 'price' => 80, 'stock_quantity' => 100]);
    }

    /**
     * @param  array<int, array{0: Product, 1: int}>  $items
     */
    private function tableOrder(int $number, array $items, ?string $note = null): Order
    {
        $table = DiningTable::factory()->create(['number' => $number]);
        $this->actingAs($this->waiter)->post(route('tables.open-order', $table));
        $order = $table->openOrder()->firstOrFail();

        foreach ($items as [$product, $quantity]) {
            $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $product->id, 'quantity' => $quantity]);
        }

        if ($note) {
            $order->update(['note' => $note]);
        }

        return $order->fresh();
    }

    public function test_order_moves_to_an_empty_table(): void
    {
        $order = $this->tableOrder(4, [[$this->tea, 2]]);
        $old = $order->diningTable;
        $target = DiningTable::factory()->create(['number' => 9]);

        $this->actingAs($this->waiter)
            ->post(route('orders.move', $order), ['dining_table_id' => $target->id])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertSame($target->id, $order->fresh()->dining_table_id);
        $this->assertSame(TableStatus::Bos, $old->fresh()->status);
        $this->assertSame(TableStatus::Dolu, $target->fresh()->status);
        $this->assertEquals(30, $order->fresh()->total);
        $this->assertDatabaseHas('activity_logs', ['action' => 'siparis_tasindi', 'subject_id' => $order->id]);
    }

    public function test_order_can_not_move_to_an_occupied_table_and_takeaways_can_not_move(): void
    {
        $order = $this->tableOrder(4, [[$this->tea, 1]]);
        $busy = $this->tableOrder(5, [[$this->toast, 1]]);

        $this->actingAs($this->waiter)
            ->post(route('orders.move', $order), ['dining_table_id' => $busy->dining_table_id])
            ->assertSessionHas('error');
        $this->assertSame(4, $order->fresh()->diningTable->number);

        $this->actingAs($this->waiter)->post(route('orders.store'), ['type' => 'paket']);
        $takeaway = Order::where('type', 'paket')->firstOrFail();
        $this->actingAs($this->waiter)
            ->post(route('orders.move', $takeaway), ['dining_table_id' => DiningTable::factory()->create()->id])
            ->assertSessionHas('error', 'Yalnızca masa siparişleri başka masaya taşınabilir.');
    }

    public function test_merging_moves_all_items_closes_source_and_frees_its_table(): void
    {
        $source = $this->tableOrder(4, [[$this->tea, 2], [$this->toast, 1]], 'Tost soğansız');
        $cancelled = $source->items()->where('product_id', $this->toast->id)->firstOrFail();
        $this->actingAs($this->waiter)->post(route('orders.items.cancel', [$source, $cancelled]), ['reason' => 'Vazgeçti']);
        $target = $this->tableOrder(2, [[$this->toast, 1]]);

        $this->actingAs($this->waiter)
            ->post(route('orders.merge', $source), ['target_order_id' => $target->id])
            ->assertRedirect(route('orders.show', $target))
            ->assertSessionHas('success');

        $source->refresh();
        $target->refresh();

        // Toplamlar trigger'larla yeniden hesaplandı: hedef 80 + 30 = 110, kaynak 0
        $this->assertEquals(110, $target->total);
        $this->assertEquals(0, $source->total);
        $this->assertSame(3, $target->items()->count());
        $this->assertSame(1, $target->items()->where('status', OrderItemStatus::Iptal)->count());

        $this->assertSame(OrderStatus::Birlesti, $source->status);
        $this->assertSame($target->id, $source->merged_into_id);
        $this->assertNotNull($source->closed_at);
        $this->assertSame(TableStatus::Bos, $source->diningTable->status);
        $this->assertSame(TableStatus::Dolu, $target->diningTable->status);
        $this->assertStringContainsString('Tost soğansız', $target->note);
        $this->assertFalse(Order::open()->whereKey($source->id)->exists());

        $this->actingAs($this->waiter)->get(route('orders.show', $source))->assertOk()->assertSee('hesabına birleştirildi');
        $this->actingAs($this->waiter)->get(route('orders.show', $target))->assertOk()->assertSee('Bu hesaba birleştirilenler');
    }

    public function test_merge_keeps_the_target_in_the_earlier_preparation_step(): void
    {
        $source = $this->tableOrder(4, [[$this->tea, 1]]);
        $target = $this->tableOrder(2, [[$this->toast, 1]]);
        $this->actingAs($this->waiter)->post(route('orders.advance', $target), ['from' => 'yeni']);
        $this->actingAs($this->waiter)->post(route('orders.advance', $target), ['from' => 'hazirlaniyor']);
        $this->assertSame(OrderStatus::Hazir, $target->fresh()->status);

        $this->actingAs($this->waiter)->post(route('orders.merge', $source), ['target_order_id' => $target->id]);

        // Kaynaktaki çay henüz hazırlanmadı: birleşik hesap mutfakta yeniden görünmeli
        $this->assertSame(OrderStatus::Yeni, $target->fresh()->status);
    }

    public function test_paid_orders_closed_targets_and_self_merge_are_rejected(): void
    {
        $source = $this->tableOrder(4, [[$this->tea, 2]]);
        $target = $this->tableOrder(2, [[$this->toast, 1]]);

        $this->actingAs($this->waiter)->post(route('orders.merge', $source), ['target_order_id' => $source->id])->assertSessionHas('error');

        $source->update(['paid_total' => 10]);
        $this->actingAs($this->waiter)->post(route('orders.merge', $source), ['target_order_id' => $target->id])->assertSessionHas('error');
        $source->update(['paid_total' => 0]);

        $target->update(['status' => OrderStatus::Kapandi]);
        $this->actingAs($this->waiter)->post(route('orders.merge', $source), ['target_order_id' => $target->id])->assertSessionHas('error');

        $this->assertTrue($source->fresh()->isOpen());
        $this->assertSame(1, $source->items()->count());
    }

    public function test_cashier_can_not_move_or_merge(): void
    {
        $order = $this->tableOrder(4, [[$this->tea, 1]]);
        $cashier = User::factory()->role(UserRole::Kasiyer)->create();

        $this->actingAs($cashier)->post(route('orders.move', $order), ['dining_table_id' => DiningTable::factory()->create()->id])->assertForbidden();
        $this->actingAs($cashier)->get(route('orders.show', $order))->assertOk()->assertDontSee('Hesap Birleştir');
        $this->actingAs($this->waiter)->get(route('orders.show', $order))->assertOk()->assertSee('Hesap Birleştir')->assertSee('Masayı Değiştir');
    }
}
