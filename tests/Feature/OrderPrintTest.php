<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPrintTest extends TestCase
{
    use RefreshDatabase;

    private User $waiter;

    private Product $tea;

    private Product $toast;

    protected function setUp(): void
    {
        parent::setUp();

        config(['kafe.name' => 'Demlio Moda', 'kafe.address' => 'Moda Cad. No: 12', 'kafe.receipt_footer' => 'Afiyet olsun!']);

        $this->waiter = User::factory()->role(UserRole::Garson)->create(['name' => 'Ayşe Demir']);
        $this->tea = Product::factory()->create(['name' => 'Çay', 'price' => 15, 'stock_quantity' => 100]);
        $this->toast = Product::factory()->create(['name' => 'Kaşarlı Tost', 'price' => 80, 'stock_quantity' => 100]);
    }

    private function tableOrder(int $number = 4): Order
    {
        $table = DiningTable::factory()->create(['number' => $number]);
        $this->actingAs($this->waiter)->post(route('tables.open-order', $table));

        return $table->openOrder()->firstOrFail();
    }

    private function addItem(Order $order, Product $product, int $quantity): void
    {
        $this->actingAs($this->waiter)->post(route('orders.items.store', $order), ['product_id' => $product->id, 'quantity' => $quantity]);
    }

    public function test_receipt_groups_lines_and_shows_payments_and_remaining_amount(): void
    {
        $order = $this->tableOrder();
        $this->addItem($order, $this->tea, 2);
        $this->addItem($order, $this->toast, 1);
        $this->addItem($order, $this->tea, 1);

        $cashier = User::factory()->role(UserRole::Kasiyer)->create();
        $this->actingAs($cashier)->post(route('cash.open'), ['opening_amount' => '500']);
        $this->actingAs($cashier)->post(route('orders.payments.store', $order), ['method' => 'kart', 'amount' => '100']);

        $this->actingAs($this->waiter)
            ->get(route('orders.print.receipt', $order))
            ->assertOk()
            ->assertSeeInOrder(['Demlio Moda', 'Moda Cad. No: 12', 'Adisyon', $order->code(), 'Masa 4', 'Ayşe Demir'])
            // İki ayrı eklenen çay tek satırda: 3 × 15 = 45
            ->assertSeeInOrder(['3', 'Çay', '3 × 15,00 ₺', '45,00 ₺', 'Kaşarlı Tost', '80,00 ₺'])
            ->assertSeeInOrder(['TOPLAM', '125,00 ₺', 'Kredi Kartı', '100,00 ₺', 'Kalan', '25,00 ₺'])
            ->assertSee('Afiyet olsun!')
            ->assertSee('mali değeri yoktur');
    }

    public function test_paid_receipt_is_marked_paid_and_cancelled_items_are_left_out(): void
    {
        $order = $this->tableOrder();
        $this->addItem($order, $this->tea, 1);
        $this->addItem($order, $this->toast, 1);
        $toastItem = $order->items()->where('product_id', $this->toast->id)->firstOrFail();
        $this->actingAs($this->waiter)->post(route('orders.items.cancel', [$order, $toastItem]), ['reason' => 'Vazgeçti']);

        $cashier = User::factory()->role(UserRole::Kasiyer)->create();
        $this->actingAs($cashier)->post(route('cash.open'), ['opening_amount' => '500']);
        $this->actingAs($cashier)->post(route('orders.payments.store', $order), ['method' => 'nakit', 'amount' => '15']);

        $this->actingAs($cashier)
            ->get(route('orders.print.receipt', $order))
            ->assertOk()
            ->assertSee('Ödendi')
            ->assertSee('Kapanış')
            ->assertDontSee('Kaşarlı Tost')
            ->assertDontSee('Kalan');
    }

    public function test_receipt_of_a_merged_order_redirects_to_the_target(): void
    {
        $source = $this->tableOrder(4);
        $this->addItem($source, $this->tea, 1);
        $target = $this->tableOrder(2);
        $this->addItem($target, $this->toast, 1);

        $this->actingAs($this->waiter)->post(route('orders.merge', $source), ['target_order_id' => $target->id]);

        $this->actingAs($this->waiter)
            ->get(route('orders.print.receipt', $source))
            ->assertRedirect(route('orders.print.receipt', $target));
    }

    public function test_kitchen_ticket_has_no_prices_and_shows_note(): void
    {
        $order = $this->tableOrder();
        $this->addItem($order, $this->toast, 2);
        $order->update(['note' => 'Tostlar az pişmiş']);

        $this->actingAs($this->waiter)
            ->get(route('orders.print.kitchen', $order))
            ->assertOk()
            ->assertSeeInOrder(['Masa 4', $order->code(), '2×', 'Kaşarlı Tost', 'Not', 'Tostlar az pişmiş'])
            ->assertDontSee('₺')
            ->assertDontSee('Yalnızca sonradan eklenenler');
    }

    public function test_kitchen_ticket_can_print_only_items_added_after_preparation_started(): void
    {
        $order = $this->tableOrder();
        $this->addItem($order, $this->toast, 1);
        $this->travel(5)->minutes();
        $this->actingAs($this->waiter)->post(route('orders.advance', $order), ['from' => 'yeni']);
        $this->travel(2)->minutes();
        $this->addItem($order, $this->tea, 1);

        $this->actingAs($this->waiter)
            ->get(route('orders.print.kitchen', $order))
            ->assertOk()
            ->assertSee('Kaşarlı Tost')
            ->assertSee('+ sonradan eklendi')
            ->assertSee('Yalnızca sonradan eklenenler (1)');

        $this->actingAs($this->waiter)
            ->get(route('orders.print.kitchen', [$order, 'sadece' => 'yeni']))
            ->assertOk()
            ->assertSee('Ek sipariş')
            ->assertSee('Çay')
            ->assertDontSee('Kaşarlı Tost');
    }

    public function test_print_menu_and_board_buttons_link_to_outputs(): void
    {
        $order = $this->tableOrder();
        $this->addItem($order, $this->tea, 1);

        $this->actingAs($this->waiter)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee(route('orders.print.receipt', [$order, 'yazdir' => 1]), false)
            ->assertSee(route('orders.print.kitchen', [$order, 'yazdir' => 1]), false);

        $this->actingAs($this->waiter)
            ->get(route('preparation.index'))
            ->assertOk()
            ->assertSee(e(route('orders.print.kitchen', [$order, 'yazdir' => 1, 'kaynak' => 'hazirlik'])), false);

        $this->actingAs($this->waiter)
            ->get(route('orders.print.kitchen', [$order, 'yazdir' => 1, 'kaynak' => 'hazirlik']))
            ->assertSee('window.print()', false)
            ->assertSee(route('preparation.index'), false);
    }

    public function test_guests_can_not_print(): void
    {
        $order = $this->tableOrder();

        auth()->logout();

        $this->get(route('orders.print.receipt', $order))->assertRedirect(route('login'));
        $this->get(route('orders.print.kitchen', $order))->assertRedirect(route('login'));
    }
}
