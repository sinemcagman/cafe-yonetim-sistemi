<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Siparişin hazırlık durumunun en son ne zaman değiştiğini tutar.
 * Hazırlık ekranı bu zamana göre bekleme süresini ve sonradan eklenen kalemleri gösterir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('status_changed_at')->nullable()->after('status');
            $table->index(['status', 'status_changed_at']);
        });

        DB::table('orders')->whereNull('status_changed_at')->update(['status_changed_at' => DB::raw('opened_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'status_changed_at']);
            $table->dropColumn('status_changed_at');
        });
    }
};
