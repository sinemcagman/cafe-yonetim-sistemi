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
     * Hazırlık akışındaki bir sonraki adım: yeni → hazırlanıyor → hazır → teslim edildi.
     * Kapanış ödemeyle, iptal ayrı bir işlemle yapılır; bu yüzden onlar için null döner.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Yeni => self::Hazirlaniyor,
            self::Hazirlaniyor => self::Hazir,
            self::Hazir => self::TeslimEdildi,
            default => null,
        };
    }

    /**
     * Bu duruma geçirmek için gösterilen buton metni.
     */
    public function actionLabel(): string
    {
        return match ($this) {
            self::Hazirlaniyor => 'Hazırlamaya başla',
            self::Hazir => 'Hazır',
            self::TeslimEdildi => 'Teslim edildi',
            default => $this->label(),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Yeni => 'tabler-bell-ringing',
            self::Hazirlaniyor => 'tabler-flame',
            self::Hazir => 'tabler-circle-check',
            self::TeslimEdildi => 'tabler-truck-delivery',
            self::Kapandi => 'tabler-lock',
            self::Iptal => 'tabler-ban',
        };
    }

    /**
     * Hazırlık ekranında sütun olarak gösterilen durumlar.
     *
     * @return array<int, self>
     */
    public static function preparation(): array
    {
        return [self::Yeni, self::Hazirlaniyor, self::Hazir];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $case) => [$case->value => $case->label()])->all();
    }
}
