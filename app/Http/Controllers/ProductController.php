<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\ProductRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('kategori'), fn ($query) => $query->where('category_id', $request->integer('kategori')))
            ->when($request->filled('ara'), fn ($query) => $query->where('name', 'like', '%'.$request->string('ara').'%'))
            ->when($request->boolean('kritik'), fn ($query) => $query->lowStock())
            ->orderBy('category_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::ordered()->get(),
            'lowStockCount' => Product::active()->lowStock()->count(),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product(['is_active' => true, 'low_stock_threshold' => 5]),
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $initialStock = (int) $data['stock_quantity'];

        $product = DB::transaction(function () use ($data, $initialStock, $request) {
            $product = Product::create([...$data, 'stock_quantity' => 0]);

            if ($initialStock > 0) {
                $product->adjustStock($initialStock, StockMovementType::Giris, $request->user(), 'Başlangıç stoğu');
            }

            return $product;
        });

        ActivityLog::record('urun_eklendi', "{$product->name} ürünü eklendi.", $product);

        return redirect()->route('products.index')->with('success', 'Ürün eklendi.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::ordered()->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        ActivityLog::record('urun_guncellendi', "{$product->name} ürünü güncellendi.", $product, $product->getChanges());

        return redirect()->route('products.index')->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (OrderItem::where('product_id', $product->id)->exists()) {
            return back()->with('error', 'Bu ürün siparişlerde kullanıldığı için silinemez. Satışını durdurmak için pasif yapabilirsiniz.');
        }

        $product->delete();

        ActivityLog::record('urun_silindi', "{$product->name} ürünü silindi.");

        return redirect()->route('products.index')->with('success', 'Ürün silindi.');
    }
}
