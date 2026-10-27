<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Yeni = 'yeni';
    case Hazirlaniyor = 'hazirlaniyor';
    case Hazir = 'hazir';
    case TeslimEdildi = 'teslim_edildi';
    case Kapandi = 'kapandi';
    case Iptal = 'iptal';

    public function label(): string
    {
        return match ($this) {
            self::Yeni => 'Yeni',
            self::Hazirlaniyor => 'Hazırlanıyor',
            self::Hazir => 'Hazır',
            self::TeslimEdildi => 'Teslim Edildi',
            self::Kapandi => 'Kapandı',
            self::Iptal => 'İptal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Yeni => 'secondary',
            self::Hazirlaniyor => 'warning',
            self::Hazir => 'info',
            self::TeslimEdildi => 'primary',
            self::Kapandi => 'success',
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
