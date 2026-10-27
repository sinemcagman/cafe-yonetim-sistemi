<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['type', 'dining_table_id', 'user_id', 'status', 'note', 'total', 'paid_total', 'opened_at', 'closed_at'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'total' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<DiningTable, $this>
     */
    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function activeItems(): HasMany
    {
        return $this->items()->where('status', OrderItemStatus::Aktif);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', [OrderStatus::Kapandi, OrderStatus::Iptal]);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [OrderStatus::Kapandi, OrderStatus::Iptal], true);
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_total);
    }

    public function recalculateTotal(): void
    {
        $this->update(['total' => $this->activeItems()->sum('line_total')]);
    }
}
