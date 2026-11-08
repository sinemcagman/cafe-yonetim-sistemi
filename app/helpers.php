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
     * Formdan gelen tutarı doğrulanabilir hale getirir:
     * "1.250,50" → "1250.50", "15,5" → "15.5", "1.000" → "1000" (Türkçe binlik ayırıcı), "15.50" → "15.50".
     */
    function normalize_amount(mixed $value): string
    {
        $value = str_replace(' ', '', trim((string) $value));

        if (str_contains($value, ',')) {
            return str_replace(['.', ','], ['', '.'], $value);
        }

        // Virgülsüz ve noktalar üçerli gruplar ayırıyorsa binlik ayırıcıdır: 1.000, 12.500, 1.250.000
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            return str_replace('.', '', $value);
        }

        return $value;
    }
}
