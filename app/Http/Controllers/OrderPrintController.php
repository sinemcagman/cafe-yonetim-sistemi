<?php

namespace App\Http\Controllers;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Siparişin 80 mm termal yazıcı çıktıları: müşteri adisyonu ve mutfak/bar fişi.
 */
class OrderPrintController extends Controller
{
    public function receipt(Order $order): View|RedirectResponse
    {
        // Birleştirilmiş hesabın kalemleri ve ödemesi hedef hesapta
        if ($order->status === OrderStatus::Birlesti && $order->merged_into_id) {
            return redirect()->route('orders.print.receipt', $order->merged_into_id);
        }

        $order->load(['diningTable', 'user', 'items.product', 'payments' => fn ($q) => $q->oldest('paid_at')]);

        // Aynı ürün aynı fiyattan birden çok kez eklendiyse adisyonda tek satırda toplanır
        $lines = $order->items
            ->where('status', OrderItemStatus::Aktif)
            ->groupBy(fn (OrderItem $item) => $item->product_id.'|'.$item->unit_price)
            ->map(fn ($items) => [
                'name' => $items->first()->product->name,
                'quantity' => $items->sum('quantity'),
                'unit_price' => (float) $items->first()->unit_price,
                'total' => (float) $items->sum('line_total'),
            ])
            ->values();

        return view('orders.print.receipt', [
            'order' => $order,
            'lines' => $lines,
            'backUrl' => route('orders.show', $order),
        ]);
    }

    /**
     * Mutfak/bar fişi: fiyatsız, büyük yazılı kalem listesi ve sipariş notu.
     * ?sadece=yeni ile yalnızca son durum değişikliğinden sonra eklenen kalemler basılır.
     */
    public function kitchen(Request $request, Order $order): View
    {
        $order->load(['diningTable', 'user', 'items' => fn ($q) => $q->with('product')->oldest()]);

        $lateIds = $order->lateItemIds();
        $onlyLate = $request->input('sadece') === 'yeni' && $lateIds->isNotEmpty();

        $items = $order->items
            ->where('status', OrderItemStatus::Aktif)
            ->when($onlyLate, fn ($items) => $items->whereIn('id', $lateIds));

        return view('orders.print.kitchen', [
            'order' => $order,
            'items' => $items,
            'lateIds' => $lateIds,
            'onlyLate' => $onlyLate,
            // Hazırlık panosundan açılan fiş "Geri dön" ile panoya döner
            'backUrl' => $request->input('kaynak') === 'hazirlik' ? route('preparation.index') : route('orders.show', $order),
        ]);
    }
}
