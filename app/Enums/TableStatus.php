<?php

namespace App\Enums;

enum TableStatus: string
{
    case Bos = 'bos';
    case Dolu = 'dolu';

    public function label(): string
    {
        return match ($this) {
            self::Bos => 'Boş',
            self::Dolu => 'Kullanımda',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Bos => 'success',
            self::Dolu => 'danger',
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
