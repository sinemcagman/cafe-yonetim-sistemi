<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderStatusController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    /**
     * Siparişi hazırlık akışında bir sonraki adıma taşır.
     * "from" alanı ekranda görünen durumdur; arada değiştiyse işlem yapılmaz.
     */
    public function advance(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['from' => ['nullable', Rule::enum(OrderStatus::class)]]);

        try {
            $order = $this->orders->advance($order, $request->user(), isset($data['from']) ? OrderStatus::from($data['from']) : null);
            $ok = true;
            $message = "{$order->label()} → {$order->status->label()}";
        } catch (DomainException $e) {
            $ok = false;
            $message = $e->getMessage();
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }
}
