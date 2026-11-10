<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Müşterilerin masadaki QR kodu okutarak açtığı, girişsiz ve salt okunur menü.
 */
class MenuController extends Controller
{
    public function __invoke(Request $request): View
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->with(['products' => fn ($q) => $q->active()->orderBy('name')])
            ->get()
            ->filter(fn (Category $category) => $category->products->isNotEmpty())
            ->values();

        // QR kartındaki ?masa=4 bilgisi yalnızca karşılama için kullanılır
        $table = $request->filled('masa')
            ? DiningTable::active()->where('number', (int) $request->input('masa'))->first()
            : null;

        return view('menu.show', [
            'categories' => $categories,
            'table' => $table,
        ]);
    }
}
