<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Nakit = 'nakit';
    case Kart = 'kart';

    public function label(): string
    {
        return match ($this) {
            self::Nakit => 'Nakit',
            self::Kart => 'Kredi Kartı',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Nakit => 'success',
            self::Kart => 'primary',
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
