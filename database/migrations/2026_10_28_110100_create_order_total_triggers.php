<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sipariş kalemi eklendiğinde, değiştiğinde (ör. iptal edildiğinde) ya da silindiğinde
 * siparişin toplam tutarını veritabanı seviyesinde yeniden hesaplayan trigger'lar.
 * Toplama yalnızca iptal edilmemiş ("aktif") kalemler dahil edilir.
 */
return new class extends Migration
{
    private const RECALCULATE = <<<'SQL'
        UPDATE orders
        SET total = (
            SELECT COALESCE(SUM(oi.line_total), 0)
            FROM order_items oi
            WHERE oi.order_id = orders.id AND oi.status = 'aktif'
        ),
        updated_at = NOW()
        WHERE id = %s;
        SQL;

    public function up(): void
    {
        $this->down();

        DB::unprepared('CREATE TRIGGER trg_order_items_after_insert AFTER INSERT ON order_items FOR EACH ROW '
            .sprintf(self::RECALCULATE, 'NEW.order_id'));

        DB::unprepared("CREATE TRIGGER trg_order_items_after_update AFTER UPDATE ON order_items FOR EACH ROW\nBEGIN\n"
            .sprintf(self::RECALCULATE, 'NEW.order_id')
            ."\nIF OLD.order_id <> NEW.order_id THEN\n".sprintf(self::RECALCULATE, 'OLD.order_id')."\nEND IF;\nEND");

        DB::unprepared('CREATE TRIGGER trg_order_items_after_delete AFTER DELETE ON order_items FOR EACH ROW '
            .sprintf(self::RECALCULATE, 'OLD.order_id'));
    }

    public function down(): void
    {
        foreach (['insert', 'update', 'delete'] as $event) {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_order_items_after_{$event}");
        }
    }
};
