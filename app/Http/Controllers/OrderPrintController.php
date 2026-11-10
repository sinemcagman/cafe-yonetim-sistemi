<?php

namespace App\Http\Controllers;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
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
}
