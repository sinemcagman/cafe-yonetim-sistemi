<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use DomainException;
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
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('stock-movements.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'types' => StockMovementType::cases(),
        ]);
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
