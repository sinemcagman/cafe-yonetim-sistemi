<?php

if (! function_exists('money')) {
    /**
     * Tutarı Türk lirası biçiminde gösterir: 1.234,50 ₺
     */
    function money(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2, ',', '.').' ₺';
    }
}
