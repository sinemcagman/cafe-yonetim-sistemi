<?php

namespace App\Models;

use App\Enums\StockMovementType;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['category_id', 'name', 'description', 'price', 'stock_quantity', 'low_stock_threshold', 'is_active'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): void
    {
        $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isSellable(int $quantity = 1): bool
    {
        return $this->is_active && $this->stock_quantity >= $quantity;
    }

    /**
     * Ürün adedini değiştirir ve değişikliği stok hareketi olarak kaydeder.
     * Pozitif miktar stok ekler, negatif miktar stoktan düşer.
     */
    public function adjustStock(
        int $quantity,
        StockMovementType $type,
        ?User $user = null,
        ?string $reason = null,
        ?OrderItem $orderItem = null,
    ): StockMovement {
        return DB::transaction(function () use ($quantity, $type, $user, $reason, $orderItem) {
            $product = static::query()->lockForUpdate()->findOrFail($this->getKey());
            $before = $product->stock_quantity;
            $after = $before + $quantity;

            if ($after < 0) {
                throw new DomainException("{$product->name} için stokta yeterli adet yok (mevcut: {$before}).");
            }

            $product->update(['stock_quantity' => $after]);
            $this->stock_quantity = $after;

            return $product->stockMovements()->create([
                'user_id' => $user?->getKey(),
                'order_item_id' => $orderItem?->getKey(),
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $reason,
            ]);
        });
    }
}
