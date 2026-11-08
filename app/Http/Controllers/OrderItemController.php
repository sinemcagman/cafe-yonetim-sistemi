<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddOrderItemRequest;
use App\Http\Requests\CancelRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sipariş kalemleri. Sipariş ekranı bu uçları fetch ile çağırır ve adisyonu sayfayı yenilemeden günceller;
 * JavaScript kapalıysa normal form gönderimi gibi geri yönlendirir.
 */
class OrderItemController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function store(AddOrderItemRequest $request, Order $order): JsonResponse|RedirectResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));
        $quantity = (int) $request->validated('quantity');

        try {
            $this->orders->addItem($order, $product, $quantity, $request->user());
        } catch (DomainException $e) {
            return $this->respond($request, $order, $e->getMessage(), false);
        }

        return $this->respond($request, $order, "{$quantity} × {$product->name} eklendi.");
    }

    public function cancel(CancelRequest $request, Order $order, OrderItem $item): JsonResponse|RedirectResponse
    {
        abort_unless($item->order_id === $order->id, 404);

        try {
            $this->orders->cancelItem($item, $request->user(), $request->validated('reason'));
        } catch (DomainException $e) {
            return $this->respond($request, $order, $e->getMessage(), false);
        }

        return $this->respond($request, $order, "{$item->product->name} iptal edildi; adet stoğa geri eklendi.");
    }

    private function respond(Request $request, Order $order, string $message, bool $ok = true): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return back()->with($ok ? 'success' : 'error', $message);
        }

        $order->refresh()->load(['diningTable', 'items' => fn ($q) => $q->with(['product', 'user', 'canceller'])->oldest()]);

        return response()->json([
            'ok' => $ok,
            'message' => $message,
            'ticket' => view('orders._ticket', ['order' => $order])->render(),
            'stock' => $order->items->pluck('product')->unique('id')
                ->mapWithKeys(fn (Product $product) => [$product->id => $product->fresh()->stock_quantity]),
        ], $ok ? 200 : 422);
    }
}
