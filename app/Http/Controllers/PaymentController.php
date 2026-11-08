<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\TakePaymentRequest;
use App\Models\Order;
use App\Services\CashService;
use DomainException;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly CashService $cash)
    {
    }

    public function store(TakePaymentRequest $request, Order $order): RedirectResponse
    {
        $method = PaymentMethod::from($request->validated('method'));
        $amount = (float) $request->validated('amount');

        try {
            $this->cash->takePayment($order, $request->user(), $method, $amount);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $order->refresh();

        if (! $order->isOpen()) {
            return redirect()
                ->route($order->dining_table_id ? 'tables.index' : 'orders.index')
                ->with('success', "{$order->label()} ödendi ve kapatıldı.".($order->diningTable ? " {$order->diningTable->label()} artık boş." : ''));
        }

        return back()->with('success', money($amount).' '.mb_strtolower($method->label()).' ödeme alındı. Kalan: '.money($order->remainingAmount()));
    }
}
