<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->ordered()
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create', ['category' => new Category(['is_active' => true])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        ActivityLog::record('kategori_eklendi', "{$category->name} kategorisi eklendi.", $category);

        return redirect()->route('categories.index')->with('success', 'Kategori eklendi.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        ActivityLog::record('kategori_guncellendi', "{$category->name} kategorisi güncellendi.", $category, $category->getChanges());

        return redirect()->route('categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Bu kategoride ürün bulunduğu için silinemez. Kategoriyi pasif yapabilirsiniz.');
        }

        $category->delete();

        ActivityLog::record('kategori_silindi', "{$category->name} kategorisi silindi.");

        return redirect()->route('categories.index')->with('success', 'Kategori silindi.');
    }
}
