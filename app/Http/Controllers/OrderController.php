<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Requests\CancelRequest;
use App\Http\Requests\OpenOrderRequest;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Order;
use App\Services\CashService;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CashService $cash,
    ) {
    }

    /**
     * Açık siparişler (kart görünümü) ve sipariş geçmişi (sayfalı liste).
     */
    public function index(Request $request): View
    {
        $tab = $request->input('gorunum') === 'gecmis' ? 'gecmis' : 'acik';

        $filters = fn ($query) => $query
            ->when($request->filled('tur'), fn ($q) => $q->where('type', $request->string('tur')))
            ->when($request->filled('ara'), fn ($q) => $q->whereKey((int) ltrim((string) $request->string('ara'), '#0')));

        $openOrders = Order::query()
            ->open()
            ->tap($filters)
            ->with(['diningTable', 'user'])
            ->withCount('activeItems')
            ->oldest('opened_at')
            ->get();

        $history = $tab === 'gecmis'
            ? Order::query()
                ->whereIn('status', [OrderStatus::Kapandi, OrderStatus::Iptal])
                ->tap($filters)
                ->when($this->dateRange($request), fn ($q, array $range) => $q->whereBetween('opened_at', $range))
                ->with(['diningTable', 'user'])
                ->withCount('activeItems')
                ->latest('opened_at')
                ->paginate($this->perPage($request))
                ->withQueryString()
            : null;

        return view('orders.index', [
            'tab' => $tab,
            'openOrders' => $openOrders,
            'history' => $history,
            'types' => OrderType::cases(),
            'availableTables' => DiningTable::active()->where('status', 'bos')->orderBy('number')->get(),
        ]);
    }

    public function store(OpenOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $type = OrderType::from($data['type']);

        try {
            $order = $type === OrderType::Masa
                ? $this->orders->openForTable(DiningTable::findOrFail($data['dining_table_id']), $request->user(), $data['note'] ?? null)
                : $this->orders->openTakeaway($type, $request->user(), $data['note'] ?? null);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', "{$order->label()} açıldı. Ürünleri ekleyebilirsiniz.");
    }

    /**
     * Masalar ekranından tek tıkla sipariş açma (masada açık sipariş varsa ona gider).
     */
    public function openForTable(Request $request, DiningTable $table): RedirectResponse
    {
        try {
            $order = $this->orders->openForTable($table, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order);
    }

    /**
     * Sipariş ekranı: solda menü, sağda adisyon.
     */
    public function show(Order $order): View
    {
        $order->load(['diningTable', 'user', 'items' => fn ($q) => $q->with(['product', 'user', 'canceller'])->oldest()]);

        return view('orders.show', [
            'order' => $order,
            'cashSession' => $this->cash->currentSession(),
            'categories' => Category::query()
                ->active()
                ->ordered()
                ->with(['products' => fn ($q) => $q->orderBy('name')])
                ->get()
                ->filter(fn (Category $category) => $category->products->isNotEmpty())
                ->values(),
        ]);
    }

    public function updateNote(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:500']], [], ['note' => 'sipariş notu']);

        try {
            $this->orders->updateNote($order, $data['note'] ?? null, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Sipariş notu kaydedildi.');
    }

    public function cancel(CancelRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->orders->cancelOrder($order, $request->user(), $request->validated('reason'));
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', "{$order->label()} iptal edildi; ürün adetleri stoğa geri eklendi.");
    }
}
