<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Exceptions\InvalidFormatException;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->when($request->filled('urun'), fn ($query) => $query->where('product_id', $request->integer('urun')))
            ->when($request->filled('tur'), fn ($query) => $query->where('type', $request->string('tur')))
            ->when($this->dateRange($request), fn ($query, array $range) => $query->whereBetween('created_at', $range))
            ->latest()
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('stock-movements.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'types' => StockMovementType::cases(),
        ]);
    }

    /**
     * Tarih seçiciden gelen "2026-10-01 - 2026-10-28" ya da "2026-10-28" değerini gün başı/sonu aralığına çevirir.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function dateRange(Request $request): ?array
    {
        preg_match_all('/\d{4}-\d{2}-\d{2}/', (string) $request->input('tarih'), $matches);
        $dates = $matches[0];

        if ($dates === []) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $dates[1] ?? $dates[0])->endOfDay();
        } catch (InvalidFormatException) {
            return null;
        }

        return $start->lte($end) ? [$start, $end] : [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
    }

    public function store(StockAdjustmentRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $type = StockMovementType::from($data['type']);

        try {
            $movement = $product->adjustStock((int) $data['quantity'], $type, $request->user(), $data['reason']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::record('stok_guncellendi', "{$product->name} stoğu {$movement->quantity_before} → {$movement->quantity_after} olarak güncellendi.", $product, [
            'tur' => $type->value,
            'adet' => $movement->quantity,
            'neden' => $movement->reason,
        ]);

        return back()->with('success', "{$product->name} stoğu güncellendi. Yeni adet: {$movement->quantity_after}");
    }
}
