<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Product $tea;

    private Product $toast;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-10 18:00'));

        $this->manager = User::factory()->role(UserRole::Yonetici)->create();
        $drinks = Category::factory()->create(['name' => 'Sıcak İçecekler']);
        $food = Category::factory()->create(['name' => 'Yiyecekler']);
        $this->tea = Product::factory()->for($drinks)->create(['name' => 'Çay', 'price' => 15]);
        $this->toast = Product::factory()->for($food)->create(['name' => 'Tost', 'price' => 80]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Verilen anda açılıp ödenen bir paket siparişi oluşturur.
     *
     * @param  array<int, array{0: Product, 1: int}>  $items
     */
    private function sale(string $at, array $items, PaymentMethod $method = PaymentMethod::Nakit, OrderStatus $status = OrderStatus::Kapandi): Order
    {
        $time = Carbon::parse($at);
        $order = Order::create([
            'type' => OrderType::Paket, 'user_id' => $this->manager->id, 'status' => $status,
            'opened_at' => $time, 'closed_at' => $status === OrderStatus::Kapandi ? $time->copy()->addMinutes(20) : null,
        ]);

        foreach ($items as [$product, $quantity]) {
            $order->items()->create([
                'product_id' => $product->id, 'user_id' => $this->manager->id, 'quantity' => $quantity,
                'unit_price' => $product->price, 'line_total' => $product->price * $quantity,
            ]);
        }

        $order->refresh();

        if ($status === OrderStatus::Kapandi) {
            Payment::create(['order_id' => $order->id, 'user_id' => $this->manager->id, 'method' => $method, 'amount' => $order->total, 'paid_at' => $time->copy()->addMinutes(20)]);
            $order->update(['paid_total' => $order->total]);
        }

        return $order;
    }

    public function test_summary_compares_revenue_with_previous_period(): void
    {
        // Önceki 7 gün (28 Ekim – 3 Kasım): 95 + 15 = 110 ₺
        $this->sale('2026-11-02 10:00', [[$this->toast, 1], [$this->tea, 1]], PaymentMethod::Kart);  // 95
        $this->sale('2026-11-03 10:00', [[$this->tea, 1]], PaymentMethod::Nakit);                    // 15

        // Bu 7 gün (4 – 10 Kasım): 170 ₺ → nakit 110 + 15 = 125, kart 15 + 30 = 45
        $this->sale('2026-11-05 09:15', [[$this->tea, 2], [$this->toast, 1]], PaymentMethod::Nakit); // 110
        $this->sale('2026-11-10 09:40', [[$this->tea, 1]], PaymentMethod::Kart);                     // 15
        $this->sale('2026-11-10 13:05', [[$this->tea, 1]], PaymentMethod::Nakit);                    // 15
        $this->sale('2026-11-10 13:30', [[$this->tea, 2]], PaymentMethod::Kart);                     // 30

        $summary = ReportService::forRange(Carbon::parse('2026-11-04'), Carbon::parse('2026-11-10'))->summary();

        $this->assertEquals(170, $summary['revenue']);
        $this->assertEquals(110, $summary['previous_revenue']);
        $this->assertEquals(54.5, $summary['change']);
        $this->assertEquals(125, $summary['cash']);
        $this->assertEquals(45, $summary['card']);
        $this->assertSame(4, $summary['closed_orders']);
        $this->assertEquals(42.5, $summary['average_ticket']);
    }

    public function test_daily_revenue_fills_days_without_payments(): void
    {
        $this->sale('2026-11-08 12:00', [[$this->toast, 2]]);

        $daily = ReportService::forRange(Carbon::parse('2026-11-07'), Carbon::parse('2026-11-09'))->dailyRevenue();

        $this->assertSame(['2026-11-07', '2026-11-08', '2026-11-09'], $daily->pluck('date')->all());
        $this->assertSame([0.0, 160.0, 0.0], $daily->pluck('total')->all());
    }

    public function test_top_products_and_categories_exclude_cancelled_orders(): void
    {
        $this->sale('2026-11-10 10:00', [[$this->tea, 5], [$this->toast, 1]]);
        $this->sale('2026-11-10 11:00', [[$this->toast, 2]]);
        $this->sale('2026-11-10 12:00', [[$this->toast, 9]], status: OrderStatus::Iptal);

        $report = ReportService::forRange(today(), today());

        $top = $report->topProducts();
        $this->assertSame(['Çay', 'Tost'], $top->pluck('name')->all());
        $this->assertSame([5, 3], $top->pluck('quantity')->all());

        $categories = $report->categoryRevenue();
        $this->assertSame('Yiyecekler', $categories->first()->name);
        $this->assertEquals(240, $categories->first()->revenue);
        $this->assertEquals(75, $categories->last()->revenue);
    }

    public function test_hourly_orders_cover_only_active_hours(): void
    {
        $this->sale('2026-11-10 09:10', [[$this->tea, 1]]);
        $this->sale('2026-11-10 12:20', [[$this->tea, 1]]);
        $this->sale('2026-11-10 12:50', [[$this->tea, 1]]);

        $hourly = ReportService::forRange(today(), today())->hourlyOrders();

        $this->assertSame(['09:00', '10:00', '11:00', '12:00'], $hourly->pluck('label')->all());
        $this->assertSame([1, 0, 0, 2], $hourly->pluck('orders')->all());
    }

    public function test_reports_page_is_for_managers_and_supports_presets_and_ranges(): void
    {
        $this->sale('2026-11-10 12:20', [[$this->toast, 1]]);
        $cashier = User::factory()->role(UserRole::Kasiyer)->create();

        $this->actingAs($cashier)->get(route('reports.index'))->assertForbidden();

        $this->actingAs($this->manager)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Son 7 gün')
            ->assertSee('80,00 ₺')
            ->assertSee('en yoğun saat 12:00');

        $this->actingAs($this->manager)->get(route('reports.index', ['donem' => 'bugun']))->assertOk()->assertSee('Tost');

        $this->actingAs($this->manager)->get(route('reports.index', ['tarih' => '2026-10-01 - 2026-10-05']))
            ->assertOk()
            ->assertSee('Bu dönemde veri yok');
    }
}
