<?php

namespace Tests\Feature;

use App\Enums\OrderItemStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseObjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_total_is_kept_in_sync_by_triggers(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $order = Order::create(['type' => OrderType::Paket, 'user_id' => $user->id, 'opened_at' => now()]);

        $item = $order->items()->create([
            'product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 2, 'unit_price' => 15, 'line_total' => 30,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'user_id' => $user->id, 'quantity' => 1, 'unit_price' => 80, 'line_total' => 80,
        ]);
        $this->assertEquals(110, $order->fresh()->total);

        $item->update(['status' => OrderItemStatus::Iptal, 'cancelled_at' => now()]);
        $this->assertEquals(80, $order->fresh()->total);

        $order->items()->where('status', OrderItemStatus::Aktif)->delete();
        $this->assertEquals(0, $order->fresh()->total);
    }

    public function test_stock_status_view_classifies_products(): void
    {
        Product::factory()->create(['name' => 'Tükenen', 'stock_quantity' => 0, 'low_stock_threshold' => 5, 'price' => 10]);
        Product::factory()->create(['name' => 'Azalan', 'stock_quantity' => 3, 'low_stock_threshold' => 5, 'price' => 10]);
        Product::factory()->create(['name' => 'Yeterli', 'stock_quantity' => 30, 'low_stock_threshold' => 5, 'price' => 10]);

        $statuses = DB::table('v_urun_stok_durumu')->pluck('stok_durumu', 'urun');

        $this->assertSame('tukendi', $statuses['Tükenen']);
        $this->assertSame('kritik', $statuses['Azalan']);
        $this->assertSame('yeterli', $statuses['Yeterli']);
        $this->assertEquals(300, DB::table('v_urun_stok_durumu')->where('urun', 'Yeterli')->value('stok_degeri'));
    }

    public function test_stock_status_function_returns_expected_labels(): void
    {
        $this->assertSame('tukendi', DB::scalar('SELECT fn_stok_durumu(0, 5)'));
        $this->assertSame('kritik', DB::scalar('SELECT fn_stok_durumu(5, 5)'));
        $this->assertSame('yeterli', DB::scalar('SELECT fn_stok_durumu(6, 5)'));
    }

    public function test_daily_cash_report_procedure_groups_payments_by_method(): void
    {
        $user = User::factory()->create();
        $order = Order::create(['type' => OrderType::Paket, 'user_id' => $user->id, 'opened_at' => now()]);

        foreach ([['nakit', 50], ['nakit', 25], ['kart', 30]] as [$method, $amount]) {
            Payment::create(['order_id' => $order->id, 'user_id' => $user->id, 'method' => $method, 'amount' => $amount, 'paid_at' => now()]);
        }
        Payment::create(['order_id' => $order->id, 'user_id' => $user->id, 'method' => 'kart', 'amount' => 999, 'paid_at' => now()->subDay()]);

        $report = collect(DB::select('CALL sp_gunluk_kasa_raporu(?)', [today()->toDateString()]))->keyBy('odeme_yontemi');

        $this->assertEquals(75, $report['nakit']->toplam_tutar);
        $this->assertEquals(2, $report['nakit']->odeme_sayisi);
        $this->assertEquals(30, $report['kart']->toplam_tutar);
    }
}
