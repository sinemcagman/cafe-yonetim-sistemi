<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case Aktif = 'aktif';
    case Iptal = 'iptal';

    public function label(): string
    {
        return match ($this) {
            self::Aktif => 'Aktif',
            self::Iptal => 'İptal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Aktif => 'success',
            self::Iptal => 'danger',
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
