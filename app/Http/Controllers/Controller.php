<?php

namespace App\Http\Controllers;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * Listelerde sayfa başına seçilebilen kayıt sayıları (ilk değer varsayılandır).
     */
    public const PER_PAGE_OPTIONS = [10, 25, 50];

    /**
     * "?adet=25" parametresini izin verilen değerlerle sınırlayarak döner.
     */
    protected function perPage(Request $request): int
    {
        $perPage = $request->integer('adet');

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : self::PER_PAGE_OPTIONS[0];
    }

    /**
     * Tarih seçiciden gelen "2026-10-01 - 2026-10-28" ya da "2026-10-28" değerini gün başı/sonu aralığına çevirir.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    protected function dateRange(Request $request, string $key = 'tarih'): ?array
    {
        preg_match_all('/\d{4}-\d{2}-\d{2}/', (string) $request->input($key), $matches);
        $dates = $matches[0];

        if ($dates === []) {
            return null;
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $dates[1] ?? $dates[0])->endOfDay();
        } catch (InvalidFormatException) {
            return null;
        }

        return $start->lte($end) ? [$start, $end] : [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
    }
}
