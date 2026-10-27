<?php

namespace App\Enums;

enum OrderType: string
{
    case Masa = 'masa';
    case Paket = 'paket';
    case GelAl = 'gel_al';

    public function label(): string
    {
        return match ($this) {
            self::Masa => 'Masa',
            self::Paket => 'Paket',
            self::GelAl => 'Gel-Al',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Masa => 'primary',
            self::Paket => 'info',
            self::GelAl => 'warning',
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
