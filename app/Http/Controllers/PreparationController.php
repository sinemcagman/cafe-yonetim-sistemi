<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Hazırlık ekranı (mutfak / bar): yeni, hazırlanıyor ve hazır siparişler sütunlar halinde.
 * Ekran birkaç saniyede bir yalnızca panoyu yeniden çizerek kendini günceller.
 */
class PreparationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $columns = $this->columns($request);

        if ($request->expectsJson()) {
            return response()->json([
                'board' => view('preparation._board', ['columns' => $columns])->render(),
                'counts' => $columns->map->count(),
                'updatedAt' => now()->format('H:i:s'),
            ]);
        }

        return view('preparation.index', [
            'columns' => $columns,
            'types' => OrderType::cases(),
        ]);
    }

    /**
     * @return Collection<string, Collection<int, Order>> durum değeri => siparişler (en uzun bekleyen önce)
     */
    private function columns(Request $request): Collection
    {
        $orders = Order::query()
            ->whereIn('status', OrderStatus::preparation())
            ->when($request->filled('tur'), fn ($q) => $q->where('type', $request->string('tur')))
            ->with(['diningTable', 'user', 'items' => fn ($q) => $q->with('product')->oldest()])
            ->oldest('status_changed_at')
            ->get();

        return collect(OrderStatus::preparation())
            ->mapWithKeys(fn (OrderStatus $status) => [$status->value => $orders->where('status', $status)->values()]);
    }
}
