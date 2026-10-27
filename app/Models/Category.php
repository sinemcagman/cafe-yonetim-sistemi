<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'icon', 'sort_order', 'is_active'])]
class Category extends Model
{
    use HasFactory;

    public const DEFAULT_ICON = 'tabler-tools-kitchen-2';

    /**
     * İkon seçicide "Önerilenler" başlığıyla en üstte gösterilen kafe ikonları.
     * Bunların dışında tüm Tabler ikonları da seçilebilir (bkz. App\Support\TablerIcons).
     *
     * @var array<string, string>
     */
    public const SUGGESTED_ICONS = [
        'tabler-coffee' => 'Sıcak içecek',
        'tabler-cup' => 'Bardak',
        'tabler-glass-full' => 'Soğuk içecek',
        'tabler-bottle' => 'Şişe',
        'tabler-baguette' => 'Unlu mamul',
        'tabler-bread' => 'Ekmek',
        'tabler-egg' => 'Kahvaltı',
        'tabler-salad' => 'Salata',
        'tabler-soup' => 'Çorba',
        'tabler-pizza' => 'Pizza',
        'tabler-cake' => 'Tatlı',
        'tabler-ice-cream' => 'Dondurma',
        'tabler-cookie' => 'Kurabiye',
        'tabler-leaf' => 'Bitki çayı',
        'tabler-tools-kitchen-2' => 'Genel',
    ];

    public function iconClass(): string
    {
        // İkon kayıt sırasında doğrulanır; burada yalnızca beklenen biçimde olup olmadığına bakılır
        $icon = (string) $this->icon;

        return 'icon-base ti '.(preg_match('/^tabler-[a-z0-9-]+$/', $icon) ? $icon : self::DEFAULT_ICON);
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
