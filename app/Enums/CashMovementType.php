<?php

namespace App\Enums;

enum CashMovementType: string
{
    case Giris = 'giris';
    case Cikis = 'cikis';

    public function label(): string
    {
        return match ($this) {
            self::Giris => 'Kasa Girişi',
            self::Cikis => 'Kasa Çıkışı',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Giris => 'success',
            self::Cikis => 'danger',
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
