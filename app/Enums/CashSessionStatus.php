<?php

namespace App\Enums;

enum CashSessionStatus: string
{
    case Acik = 'acik';
    case Kapali = 'kapali';

    public function label(): string
    {
        return match ($this) {
            self::Acik => 'Açık',
            self::Kapali => 'Kapalı',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Acik => 'success',
            self::Kapali => 'secondary',
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
