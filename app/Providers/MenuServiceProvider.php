<?php

namespace App\Providers;

use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /** @var array<string, int> */
    private array $badgeCounts = [];

    /**
     * Vuexy menü JSON dosyalarını okur, giriş yapan çalışanın rolüne göre
     * süzer ve menü rozetlerini (ör. kritik stok sayısı) ekler.
     */
    public function boot(): void
    {
        View::composer('layouts.sections.menu.*', function ($view) {
            $this->badgeCounts = [];

            $view->with('menuData', [
                $this->menuFor('verticalMenu'),
                $this->menuFor('horizontalMenu'),
            ]);
        });

        View::composer('layouts.sections.navbar.navbar-partial', function ($view) {
            $isManager = (bool) auth()->user()?->isManager();

            $view->with([
                'lowStockProducts' => $isManager
                    ? Product::active()->lowStock()->with('category')->orderBy('stock_quantity')->limit(5)->get()
                    : collect(),
                'lowStockTotal' => $isManager ? Product::active()->lowStock()->count() : 0,
            ]);
        });
    }

    private function menuFor(string $file): object
    {
        $menu = json_decode(file_get_contents(resource_path("menu/{$file}.json")));
        $user = auth()->user();

        $items = collect($menu->menu)
            ->filter(fn ($item) => ! isset($item->roles) || ($user && $user->hasRole(...$item->roles)))
            ->map(function ($item) {
                if (isset($item->badgeKey) && ($count = $this->badgeCount($item->badgeKey)) > 0) {
                    $item->badge = ['danger', $count];
                }

                return $item;
            })
            ->values();

        return (object) ['menu' => $items->all()];
    }

    private function badgeCount(string $key): int
    {
        return $this->badgeCounts[$key] ??= match ($key) {
            'lowStock' => Product::active()->lowStock()->count(),
            default => 0,
        };
    }
}
