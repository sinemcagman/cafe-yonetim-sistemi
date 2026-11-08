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

if (! function_exists('normalize_amount')) {
    /**
     * Formdan gelen tutarı doğrulanabilir hale getirir: "1.250,50" → "1250.50", "15,5" → "15.5".
     * Virgül yoksa değer olduğu gibi döner (ör. "15.50").
     */
    function normalize_amount(mixed $value): string
    {
        $value = trim((string) $value);

        return str_contains($value, ',') ? str_replace(['.', ','], ['', '.'], $value) : $value;
    }
}
