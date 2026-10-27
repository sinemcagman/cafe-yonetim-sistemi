<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['number', 'area', 'capacity', 'status', 'is_active'])]
class DiningTable extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'capacity' => 'integer',
            'status' => TableStatus::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasOne<Order, $this>
     */
    public function openOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->whereNotIn('status', [OrderStatus::Kapandi, OrderStatus::Iptal])
            ->latestOfMany('opened_at');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function isAvailable(): bool
    {
        return $this->status === TableStatus::Bos;
    }

    public function label(): string
    {
        return "Masa {$this->number}";
    }
}
