<?php

namespace App\Enums;

enum UserRole: string
{
    case Yonetici = 'yonetici';
    case Garson = 'garson';
    case Kasiyer = 'kasiyer';

    public function label(): string
    {
        return match ($this) {
            self::Yonetici => 'Yönetici',
            self::Garson => 'Garson',
            self::Kasiyer => 'Kasiyer',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Yonetici => 'primary',
            self::Garson => 'info',
            self::Kasiyer => 'warning',
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
