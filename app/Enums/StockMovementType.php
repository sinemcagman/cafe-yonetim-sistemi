<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Giris = 'giris';
    case Satis = 'satis';
    case Iade = 'iade';
    case Duzeltme = 'duzeltme';

    public function label(): string
    {
        return match ($this) {
            self::Giris => 'Stok Girişi',
            self::Satis => 'Satış',
            self::Iade => 'İptal İadesi',
            self::Duzeltme => 'Düzeltme',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Giris => 'success',
            self::Satis => 'primary',
            self::Iade => 'info',
            self::Duzeltme => 'warning',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
