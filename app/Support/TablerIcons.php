<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Arayüzdeki tüm Tabler ikonlarının listesi.
 * Liste public/assets/json/tabler-icons.json dosyasından okunur (ikon seçici de aynı dosyayı kullanır).
 */
class TablerIcons
{
    public const PATH = 'assets/json/tabler-icons.json';

    /**
     * @return array<int, string> "coffee", "cup" gibi önek olmadan ikon adları
     */
    public static function all(): array
    {
        $path = public_path(self::PATH);

        return Cache::rememberForever('tabler-icons:'.filemtime($path), fn () => json_decode(file_get_contents($path), true) ?: []);
    }

    /**
     * "tabler-coffee" biçimindeki bir sınıf adının geçerli bir ikon olup olmadığını döner.
     */
    public static function exists(?string $icon): bool
    {
        if (! $icon || ! str_starts_with($icon, 'tabler-')) {
            return false;
        }

        return in_array(substr($icon, strlen('tabler-')), self::all(), true);
    }
}
