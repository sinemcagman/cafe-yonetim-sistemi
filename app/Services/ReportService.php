<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Raporlar ekranının verileri. Ciro ödemelerden (tahsil edilen para), satış adetleri sipariş kalemlerinden hesaplanır.
 * Proje MySQL kullandığı için gün/saat gruplaması DATE() ve HOUR() ile veritabanında yapılır.
 */
class ReportService
{
    public function __construct(
        private readonly Carbon $from,
        private readonly Carbon $to,
    ) {
    }

    public static function forRange(Carbon $from, Carbon $to): self
    {
        return new self($from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    public function from(): Carbon
    {
        return $this->from;
    }

    public function to(): Carbon
    {
        return $this->to;
    }

    public function days(): int
    {
        return (int) $this->from->diffInDays($this->to) + 1;
    }

    /**
     * Özet kutucukları; ciro bir önceki eşit uzunluktaki dönemle karşılaştırılır.
     *
     * @return array<string, float|int|null>
     */
    public function summary(): array
    {
        $revenue = $this->revenueBetween($this->from, $this->to);
        $previousTo = $this->from->copy()->subSecond();
        $previousFrom = $previousTo->copy()->subDays($this->days() - 1)->startOfDay();
        $previous = $this->revenueBetween($previousFrom, $previousTo);

        $byMethod = Payment::query()
            ->whereBetween('paid_at', [$this->from, $this->to])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        $closedOrders = Order::query()
            ->where('status', OrderStatus::Kapandi)
            ->whereBetween('closed_at', [$this->from, $this->to])
            ->count();

        $cancelled = OrderItem::query()
            ->where('status', OrderItemStatus::Iptal)
            ->whereBetween('cancelled_at', [$this->from, $this->to]);

        return [
            'revenue' => $revenue,
            'previous_revenue' => $previous,
            'change' => $previous > 0 ? round(($revenue - $previous) / $previous * 100, 1) : null,
            'cash' => (float) ($byMethod[PaymentMethod::Nakit->value] ?? 0),
            'card' => (float) ($byMethod[PaymentMethod::Kart->value] ?? 0),
            'closed_orders' => $closedOrders,
            'average_ticket' => $closedOrders > 0 ? round($revenue / $closedOrders, 2) : 0.0,
            'cancelled_amount' => (float) (clone $cancelled)->sum('line_total'),
            'cancelled_items' => (int) (clone $cancelled)->sum('quantity'),
        ];
    }

    /**
     * Günlük ciro; ödeme olmayan günler 0 ile doldurulur.
     *
     * @return Collection<int, array{date: string, label: string, total: float}>
     */
    public function dailyRevenue(): Collection
    {
        $totals = Payment::query()
            ->whereBetween('paid_at', [$this->from, $this->to])
            ->selectRaw('DATE(paid_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(CarbonPeriod::create($this->from->copy()->startOfDay(), '1 day', $this->to->copy()->startOfDay()))
            ->map(fn (Carbon $day) => [
                'date' => $day->toDateString(),
                'label' => $day->translatedFormat($this->days() > 14 ? 'd M' : 'd M D'),
                'total' => round((float) ($totals[$day->toDateString()] ?? 0), 2),
            ])
            ->values();
    }

    /**
     * En çok satan ürünler (iptal edilmemiş kalemler, iptal edilmemiş siparişler).
     *
     * @return Collection<int, object{name: string, category: string, quantity: int, revenue: float}>
     */
    public function topProducts(int $limit = 10): Collection
    {
        return $this->soldItems()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('products.name, categories.name as category, SUM(order_items.quantity) as quantity, SUM(order_items.line_total) as revenue')
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('quantity')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (object) [
                'name' => $row->name,
                'category' => $row->category,
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ]);
    }

    /**
     * Kategoriye göre satış tutarı.
     *
     * @return Collection<int, object{name: string, quantity: int, revenue: float}>
     */
    public function categoryRevenue(): Collection
    {
        return $this->soldItems()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name, SUM(order_items.quantity) as quantity, SUM(order_items.line_total) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => (object) [
                'name' => $row->name,
                'quantity' => (int) $row->quantity,
                'revenue' => round((float) $row->revenue, 2),
            ]);
    }

    /**
     * Saat başına açılan sipariş sayısı; yalnızca sipariş görülen saat aralığı döner (ör. 08:00–23:00).
     *
     * @return Collection<int, array{hour: int, label: string, orders: int}>
     */
    public function hourlyOrders(): Collection
    {
        $counts = Order::query()
            ->where('status', '!=', OrderStatus::Iptal)
            ->whereBetween('opened_at', [$this->from, $this->to])
            ->selectRaw('HOUR(opened_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        if ($counts->isEmpty()) {
            return collect();
        }

        return collect(range((int) $counts->keys()->min(), (int) $counts->keys()->max()))
            ->map(fn (int $hour) => [
                'hour' => $hour,
                'label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00',
                'orders' => (int) ($counts[$hour] ?? 0),
            ]);
    }

    private function revenueBetween(Carbon $from, Carbon $to): float
    {
        return round((float) Payment::query()->whereBetween('paid_at', [$from, $to])->sum('amount'), 2);
    }

    private function soldItems(): \Illuminate\Database\Eloquent\Builder
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.status', OrderItemStatus::Aktif)
            ->where('orders.status', '!=', OrderStatus::Iptal)
            ->whereBetween('orders.opened_at', [$this->from, $this->to]);
    }
}
