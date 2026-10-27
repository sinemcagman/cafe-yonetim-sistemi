<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
