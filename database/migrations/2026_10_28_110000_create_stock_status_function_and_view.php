<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stok durumu için MySQL fonksiyonu ve raporlama görünümü.
 *
 *  - FUNCTION fn_stok_durumu     : adet ve kritik eşiğe göre "tukendi" / "kritik" / "yeterli" döner
 *  - VIEW     v_urun_stok_durumu : ürün, kategori, adet, stok değeri ve stok durumunu tek yerde toplar
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_stok_durumu');
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_stok_durumu(p_adet INT UNSIGNED, p_kritik_esik INT UNSIGNED)
            RETURNS VARCHAR(10)
            DETERMINISTIC
            NO SQL
            BEGIN
                IF p_adet = 0 THEN
                    RETURN 'tukendi';
                ELSEIF p_adet <= p_kritik_esik THEN
                    RETURN 'kritik';
                END IF;
                RETURN 'yeterli';
            END
            SQL);

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE VIEW v_urun_stok_durumu AS
            SELECT
                p.id AS product_id,
                p.name AS urun,
                c.id AS category_id,
                c.name AS kategori,
                p.price AS fiyat,
                p.stock_quantity AS adet,
                p.low_stock_threshold AS kritik_esik,
                ROUND(p.price * p.stock_quantity, 2) AS stok_degeri,
                fn_stok_durumu(p.stock_quantity, p.low_stock_threshold) AS stok_durumu,
                p.is_active AS satista
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_urun_stok_durumu');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_stok_durumu');
    }
};
