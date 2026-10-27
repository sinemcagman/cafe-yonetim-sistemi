<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Gün sonu kasa raporu: verilen günün tahsilatlarını ödeme yöntemine göre özetler.
 * Kullanım: CALL sp_gunluk_kasa_raporu('2026-10-28');
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_gunluk_kasa_raporu');
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_gunluk_kasa_raporu(IN p_tarih DATE)
            READS SQL DATA
            BEGIN
                SELECT
                    p.method AS odeme_yontemi,
                    COUNT(*) AS odeme_sayisi,
                    COUNT(DISTINCT p.order_id) AS siparis_sayisi,
                    COALESCE(SUM(p.amount), 0) AS toplam_tutar
                FROM payments p
                WHERE p.paid_at >= p_tarih
                  AND p.paid_at < p_tarih + INTERVAL 1 DAY
                GROUP BY p.method
                ORDER BY toplam_tutar DESC;
            END
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_gunluk_kasa_raporu');
    }
};
