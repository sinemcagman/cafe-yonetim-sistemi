<?php

namespace Tests\Feature;

use App\Enums\CashSessionStatus;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\CashSession;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    private User $waiter;

    private DiningTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->role(UserRole::Kasiyer)->create();
        $this->waiter = User::factory()->role(UserRole::Garson)->create();
        $this->table = DiningTable::factory()->create(['number' => 4]);
    }

    /**
     * Masa 4'e 2 çay (15 ₺) ve 1 tost (80 ₺) içeren 110 ₺'lik sipariş.
     */
    private function orderWorth110(): Order
    {
        $tea = Product::factory()->create(['price' => 15, 'stock_quantity' => 50]);
        $toast = Product::factory()->create(['price' => 80, 'stock_quantity' => 50]);

        $this->actingAs($this->waiter)->post(route('tables.open-order', $this->table));
        $order = Order::firstOrFail();
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $tea->id, 'quantity' => 2]);
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $toast->id, 'quantity' => 1]);

        return $order->fresh();
    }

    private function openRegister(string $amount = '500'): void
    {
        $this->actingAs($this->cashier)->post(route('cash.open'), ['opening_amount' => $amount])->assertRedirect(route('cash.index'));
    }

    public function test_payment_requires_an_open_register(): void
    {
        $order = $this->orderWorth110();

        $this->actingAs($this->cashier)
            ->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '110'])
            ->assertSessionHas('error', 'Ödeme almak için önce kasayı açın.');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_only_one_register_session_can_be_open(): void
    {
        $this->openRegister('500');

        $this->actingAs($this->cashier)->post(route('cash.open'), ['opening_amount' => '100'])->assertSessionHas('error');

        $this->assertSame(1, CashSession::count());
        $this->assertEquals(500, CashSession::first()->opening_amount);
    }

    public function test_full_payment_closes_order_and_frees_table(): void
    {
        $order = $this->orderWorth110();
        $this->openRegister();

        $this->actingAs($this->cashier)
            ->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '110,00'])
            ->assertRedirect(route('tables.index'))
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::Kapandi, $order->status);
        $this->assertNotNull($order->closed_at);
        $this->assertEquals(110, $order->paid_total);
        $this->assertSame(TableStatus::Bos, $this->table->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'method' => 'nakit', 'amount' => 110, 'user_id' => $this->cashier->id]);
        $this->assertDatabaseHas('cash_movements', ['type' => 'giris', 'method' => 'nakit', 'amount' => 110]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'odeme_alindi', 'subject_id' => $order->id]);
    }

    public function test_split_payment_keeps_order_open_until_fully_paid(): void
    {
        $order = $this->orderWorth110();
        $this->openRegister();

        $this->actingAs($this->cashier)
            ->post(route('orders.payments.store', $order), ['method' => 'kart', 'amount' => '60'])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertTrue($order->isOpen());
        $this->assertEquals(50, $order->remainingAmount());
        $this->assertSame(TableStatus::Dolu, $this->table->fresh()->status);

        $this->actingAs($this->cashier)->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '50']);

        $this->assertSame(OrderStatus::Kapandi, $order->fresh()->status);
        $this->assertSame(2, $order->payments()->count());
    }

    public function test_payment_can_not_exceed_remaining_amount(): void
    {
        $order = $this->orderWorth110();
        $this->openRegister();

        $this->actingAs($this->cashier)
            ->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '200'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
        $this->assertTrue($order->fresh()->isOpen());
    }

    public function test_waiter_can_not_take_payments_or_use_register(): void
    {
        $order = $this->orderWorth110();
        $this->openRegister();

        $this->actingAs($this->waiter)->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '110'])->assertForbidden();
        $this->actingAs($this->waiter)->get(route('cash.index'))->assertForbidden();
        $this->actingAs($this->waiter)->get(route('orders.show', $order))->assertOk()->assertDontSee('Ödeme Al');
        $this->actingAs($this->cashier)->get(route('orders.show', $order))->assertOk()->assertSee('Ödeme Al')->assertSee('Para üstü');
    }

    public function test_manual_movements_and_closing_compute_expected_cash_and_difference(): void
    {
        $order = $this->orderWorth110();
        $this->openRegister('500');
        $this->actingAs($this->cashier)->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '70']);
        $this->actingAs($this->cashier)->post(route('orders.payments.store', $order), ['method' => 'kart', 'amount' => '40']);

        $this->actingAs($this->cashier)
            ->post(route('cash.movement'), ['type' => 'cikis', 'amount' => '1.000', 'description' => 'Market alışverişi'])
            ->assertSessionHas('error');

        $this->actingAs($this->cashier)
            ->post(route('cash.movement'), ['type' => 'cikis', 'amount' => '120,50', 'description' => 'Market alışverişi'])
            ->assertSessionHas('success');

        // Beklenen nakit: 500 + 70 (nakit) − 120,50 = 449,50 (kart tahsilatı kasaya girmez)
        $this->actingAs($this->cashier)->get(route('cash.index'))->assertOk()->assertSee('449,50 ₺');

        $session = CashSession::firstOrFail();
        $this->actingAs($this->cashier)
            ->post(route('cash.close'), ['closing_amount' => '445,00', 'note' => 'Bozuk para eksik'])
            ->assertRedirect(route('cash.sessions.show', $session))
            ->assertSessionHas('error');

        $session->refresh();
        $this->assertSame(CashSessionStatus::Kapali, $session->status);
        $this->assertEquals(449.50, $session->expected_amount);
        $this->assertEquals(445.00, $session->closing_amount);
        $this->assertEquals(-4.50, $session->difference);
        $this->assertSame($this->cashier->id, $session->closed_by);

        // Kapalı kasaya ödeme alınamaz
        $this->actingAs($this->cashier)->get(route('cash.index'))->assertOk()->assertSee('Kasayı Aç');
    }

    public function test_history_shows_daily_report_from_stored_procedure(): void
    {
        $order = $this->orderWorth110();
        $this->openRegister();
        $this->actingAs($this->cashier)->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '70']);
        $this->actingAs($this->cashier)->post(route('orders.payments.store', $order), ['method' => 'kart', 'amount' => '40']);

        $session = CashSession::firstOrFail();

        $this->actingAs($this->cashier)->get(route('cash.history'))
            ->assertOk()
            ->assertSee('Günlük kasa raporu')
            ->assertSee('70,00 ₺')
            ->assertSee('40,00 ₺')
            ->assertSee('110,00 ₺');

        $this->actingAs($this->cashier)->get(route('cash.sessions.show', $session))
            ->assertOk()
            ->assertSee($order->label().' ödemesi');
    }
}
