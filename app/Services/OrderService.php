<?php

namespace App\Services;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\StockMovementType;
use App\Enums\TableStatus;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Sipariş iş kuralları: sipariş açma, ürün ekleme, kalem ve sipariş iptali.
 * Sipariş toplamı veritabanındaki trg_order_items_after_* trigger'ları tarafından güncel tutulur.
 */
class OrderService
{
    /**
     * Masaya sipariş açar. Masada zaten açık bir sipariş varsa yenisi açılmaz, mevcut sipariş döner.
     */
    public function openForTable(DiningTable $table, User $user, ?string $note = null): Order
    {
        return DB::transaction(function () use ($table, $user, $note) {
            $table = DiningTable::query()->lockForUpdate()->findOrFail($table->getKey());

            if (! $table->is_active) {
                throw new DomainException("{$table->label()} pasif durumda; sipariş açılamaz.");
            }

            if ($existing = $table->openOrder()->first()) {
                return $existing;
            }

            $order = Order::create([
                'type' => OrderType::Masa,
                'dining_table_id' => $table->id,
                'user_id' => $user->id,
                'status' => OrderStatus::Yeni,
                'status_changed_at' => now(),
                'note' => $note,
                'opened_at' => now(),
            ]);

            $table->update(['status' => TableStatus::Dolu]);

            ActivityLog::record('siparis_acildi', "{$table->label()} için {$order->code()} numaralı sipariş açıldı.", $order);

            return $order;
        });
    }

    /**
     * Masaya bağlı olmayan paket ya da gel-al siparişi açar.
     */
    public function openTakeaway(OrderType $type, User $user, ?string $note = null): Order
    {
        if ($type === OrderType::Masa) {
            throw new DomainException('Masa siparişi için bir masa seçilmelidir.');
        }

        $order = Order::create([
            'type' => $type,
            'user_id' => $user->id,
            'status' => OrderStatus::Yeni,
            'status_changed_at' => now(),
            'note' => $note,
            'opened_at' => now(),
        ]);

        ActivityLog::record('siparis_acildi', "{$order->label()} açıldı.", $order);

        return $order;
    }

    /**
     * Siparişe ürün ekler: güncel satış fiyatı siparişe aktarılır, ürün adedi stoktan düşülür.
     * Stokta yeterli adet yoksa ya da ürünün satışı durdurulmuşsa ürün eklenmez.
     */
    public function addItem(Order $order, Product $product, int $quantity, User $user): OrderItem
    {
        if ($quantity < 1) {
            throw new DomainException('Adet en az 1 olmalıdır.');
        }

        $this->ensureEditable($order);

        return DB::transaction(function () use ($order, $product, $quantity, $user) {
            $product = Product::query()->lockForUpdate()->findOrFail($product->getKey());

            if (! $product->is_active) {
                throw new DomainException("{$product->name} ürününün satışı durdurulmuş.");
            }

            if ($product->stock_quantity < $quantity) {
                throw new DomainException("{$product->name} için stokta yeterli adet yok (mevcut: {$product->stock_quantity}).");
            }

            // Hazırlanmış ya da teslim edilmiş siparişe ürün eklenirse sipariş yeniden hazırlık sırasına girer
            if (in_array($order->status, [OrderStatus::Hazir, OrderStatus::TeslimEdildi], true)) {
                $order->update(['status' => OrderStatus::Yeni, 'status_changed_at' => now()]);
            }

            $item = $order->items()->create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'line_total' => round((float) $product->price * $quantity, 2),
                'status' => OrderItemStatus::Aktif,
            ]);

            $product->adjustStock(-$quantity, StockMovementType::Satis, $user, "{$order->label()} siparişi", $item);

            ActivityLog::record('kalem_eklendi', "{$order->label()} siparişine {$quantity} × {$product->name} eklendi.", $order, [
                'urun' => $product->name,
                'adet' => $quantity,
                'birim_fiyat' => (float) $product->price,
            ]);

            return $item;
        });
    }

    /**
     * Sipariş kalemini iptal eder; düşülen adet stoğa geri eklenir, iptal bilgisi kalemde ve işlem geçmişinde kalır.
     */
    public function cancelItem(OrderItem $item, User $user, string $reason): OrderItem
    {
        $order = $item->order;
        $this->ensureEditable($order);

        if ($item->status === OrderItemStatus::Iptal) {
            throw new DomainException('Bu kalem zaten iptal edilmiş.');
        }

        return DB::transaction(function () use ($item, $order, $user, $reason) {
            $item->update([
                'status' => OrderItemStatus::Iptal,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'cancel_reason' => $reason,
            ]);

            $item->product->adjustStock($item->quantity, StockMovementType::Iade, $user, "{$order->label()} kalem iptali: {$reason}", $item);

            ActivityLog::record('kalem_iptal', "{$order->label()} siparişinde {$item->quantity} × {$item->product->name} iptal edildi.", $order, [
                'neden' => $reason,
                'tutar' => (float) $item->line_total,
            ]);

            return $item;
        });
    }

    /**
     * Ödemesi alınmamış siparişi tümüyle iptal eder: kalemler iptal edilir, stoklar geri eklenir, masa boşalır.
     */
    public function cancelOrder(Order $order, User $user, string $reason): Order
    {
        $this->ensureEditable($order);

        if ((float) $order->paid_total > 0) {
            throw new DomainException('Ödemesi alınmış bir sipariş iptal edilemez.');
        }

        return DB::transaction(function () use ($order, $user, $reason) {
            $order->activeItems()->with(['product', 'order'])->get()
                ->each(fn (OrderItem $item) => $this->cancelItem($item, $user, $reason));

            $order->update(['status' => OrderStatus::Iptal, 'closed_at' => now()]);
            $order->diningTable?->update(['status' => TableStatus::Bos]);

            ActivityLog::record('siparis_iptal', "{$order->label()} iptal edildi.", $order, ['neden' => $reason]);

            return $order->refresh();
        });
    }

    /**
     * Siparişi hazırlık akışında bir sonraki adıma taşır: yeni → hazırlanıyor → hazır → teslim edildi.
     * $expected, ekrandaki durumdur; başka biri bu arada değiştirdiyse işlem yapılmaz (çift tıklama / eşzamanlılık).
     */
    public function advance(Order $order, User $user, ?OrderStatus $expected = null): Order
    {
        return DB::transaction(function () use ($order, $user, $expected) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $this->ensureEditable($order);

            if ($expected && $order->status !== $expected) {
                throw new DomainException("{$order->label()} bu arada \"{$order->status->label()}\" durumuna alınmış; ekran yenilendi.");
            }

            $next = $order->status->next();

            if (! $next) {
                throw new DomainException("{$order->label()} hazırlık akışının sonunda.");
            }

            if ($order->status === OrderStatus::Yeni && ! $order->activeItems()->exists()) {
                throw new DomainException("{$order->label()} siparişinde ürün yok; hazırlığa alınamaz.");
            }

            $previous = $order->status;
            $order->update(['status' => $next, 'status_changed_at' => now()]);

            ActivityLog::record('siparis_durumu', "{$order->label()}: {$previous->label()} → {$next->label()}", $order, [
                'onceki' => $previous->value,
                'yeni' => $next->value,
            ]);

            return $order;
        });
    }

    public function updateNote(Order $order, ?string $note, User $user): Order
    {
        $this->ensureEditable($order);

        $order->update(['note' => $note]);

        ActivityLog::record('siparis_notu', "{$order->label()} sipariş notu güncellendi.", $order, ['not' => $note]);

        return $order;
    }

    private function ensureEditable(Order $order): void
    {
        if (! $order->isOpen()) {
            throw new DomainException("{$order->label()} kapatılmış; değişiklik yapılamaz.");
        }
    }
}
