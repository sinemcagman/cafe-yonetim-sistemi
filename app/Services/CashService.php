<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\CashSessionStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\TableStatus;
use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Kasa ve ödeme kuralları: kasa oturumu açma/kapama, ödeme alma ve kasa hareketleri.
 * Kafede tek kasa vardır; aynı anda yalnızca bir kasa oturumu açık olabilir.
 */
class CashService
{
    public function currentSession(): ?CashSession
    {
        return CashSession::open()->latest('opened_at')->first();
    }

    public function openSession(User $user, float $openingAmount, ?string $note = null): CashSession
    {
        return $this->withRegisterLock(function () use ($user, $openingAmount, $note) {
            if ($this->currentSession()) {
                throw new DomainException('Kasa zaten açık. Yeni oturum için önce mevcut kasayı kapatın.');
            }

            $session = CashSession::create([
                'opened_by' => $user->id,
                'status' => CashSessionStatus::Acik,
                'opening_amount' => round($openingAmount, 2),
                'note' => $note,
                'opened_at' => now(),
            ]);

            ActivityLog::record('kasa_acildi', 'Kasa '.money($openingAmount).' başlangıç nakdiyle açıldı.', $session);

            return $session;
        });
    }

    /**
     * Siparişin ödemesini alır. Tutar kalan borçtan fazla olamaz; tamamı ödenince sipariş kapanır ve masa boşalır.
     */
    public function takePayment(Order $order, User $user, PaymentMethod $method, float $amount): Payment
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw new DomainException('Ödeme tutarı sıfırdan büyük olmalıdır.');
        }

        return DB::transaction(function () use ($order, $user, $method, $amount) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            $session = $this->currentSession();

            if (! $session) {
                throw new DomainException('Ödeme almak için önce kasayı açın.');
            }

            if (! $order->isOpen()) {
                throw new DomainException("{$order->label()} kapatılmış; ödeme alınamaz.");
            }

            $remaining = round($order->remainingAmount(), 2);

            if ($remaining <= 0) {
                throw new DomainException("{$order->label()} için ödenecek tutar yok.");
            }

            if ($amount > $remaining) {
                throw new DomainException('Ödeme tutarı kalan borçtan ('.money($remaining).') fazla olamaz. Para üstünü nakit alanından hesaplayabilirsiniz.');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'cash_session_id' => $session->id,
                'user_id' => $user->id,
                'method' => $method,
                'amount' => $amount,
                'paid_at' => now(),
            ]);

            CashMovement::create([
                'cash_session_id' => $session->id,
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'type' => CashMovementType::Giris,
                'method' => $method,
                'amount' => $amount,
                'description' => "{$order->label()} ödemesi",
            ]);

            $order->paid_total = round((float) $order->paid_total + $amount, 2);
            $closes = $order->paid_total >= round((float) $order->total, 2);

            if ($closes) {
                $order->status = OrderStatus::Kapandi;
                $order->status_changed_at = now();
                $order->closed_at = now();
            }

            $order->save();

            if ($closes) {
                $order->diningTable?->update(['status' => TableStatus::Bos]);
            }

            ActivityLog::record('odeme_alindi', "{$order->label()} için ".money($amount)." {$method->label()} ödeme alındı".($closes ? '; hesap kapandı.' : '.'), $order, [
                'yontem' => $method->value,
                'tutar' => $amount,
                'kalan' => max(0, round((float) $order->total - (float) $order->paid_total, 2)),
            ]);

            return $payment;
        });
    }

    /**
     * Ödeme dışındaki kasa girişi / çıkışı (ör. bozuk para eklendi, market alışverişi yapıldı). Yalnızca nakit.
     */
    public function addMovement(User $user, CashMovementType $type, float $amount, string $description): CashMovement
    {
        $session = $this->currentSession() ?? throw new DomainException('Kasa hareketi için önce kasayı açın.');
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw new DomainException('Tutar sıfırdan büyük olmalıdır.');
        }

        if ($type === CashMovementType::Cikis && $amount > $this->expectedCash($session)) {
            throw new DomainException('Kasadaki nakitten ('.money($this->expectedCash($session)).') fazla çıkış yapılamaz.');
        }

        $movement = $session->movements()->create([
            'user_id' => $user->id,
            'type' => $type,
            'method' => PaymentMethod::Nakit,
            'amount' => $amount,
            'description' => $description,
        ]);

        ActivityLog::record('kasa_hareketi', "Kasa {$type->label()}: ".money($amount)." — {$description}", $session);

        return $movement;
    }

    /**
     * Kasayı kapatır: sayılan nakit ile beklenen nakit karşılaştırılır, fark kaydedilir.
     */
    public function closeSession(User $user, float $countedAmount, ?string $note = null): CashSession
    {
        return $this->withRegisterLock(function () use ($user, $countedAmount, $note) {
            $session = $this->currentSession() ?? throw new DomainException('Açık kasa oturumu yok.');
            $expected = $this->expectedCash($session);
            $counted = round($countedAmount, 2);

            $session->update([
                'status' => CashSessionStatus::Kapali,
                'closed_by' => $user->id,
                'expected_amount' => $expected,
                'closing_amount' => $counted,
                'difference' => round($counted - $expected, 2),
                'note' => trim(collect([$session->note, $note])->filter()->implode("\n")) ?: null,
                'closed_at' => now(),
            ]);

            ActivityLog::record('kasa_kapandi', 'Kasa kapatıldı. Beklenen '.money($expected).', sayılan '.money($counted).', fark '.money($counted - $expected).'.', $session);

            return $session;
        });
    }

    /**
     * Kasada olması gereken nakit: açılış + nakit girişler − nakit çıkışlar.
     */
    public function expectedCash(CashSession $session): float
    {
        $cash = $session->movements()->where('method', PaymentMethod::Nakit);

        return round(
            (float) $session->opening_amount
            + (float) (clone $cash)->where('type', CashMovementType::Giris)->sum('amount')
            - (float) (clone $cash)->where('type', CashMovementType::Cikis)->sum('amount'),
            2
        );
    }

    /**
     * Oturum özeti: ödeme yöntemlerine göre tahsilat, elle girişler/çıkışlar ve beklenen nakit.
     *
     * @return array<string, float|int>
     */
    public function summary(CashSession $session): array
    {
        $payments = $session->payments();

        return [
            'opening' => (float) $session->opening_amount,
            'cash_sales' => (float) (clone $payments)->where('method', PaymentMethod::Nakit)->sum('amount'),
            'card_sales' => (float) (clone $payments)->where('method', PaymentMethod::Kart)->sum('amount'),
            'payment_count' => (clone $payments)->count(),
            'manual_in' => (float) $session->movements()->whereNull('payment_id')->where('type', CashMovementType::Giris)->sum('amount'),
            'manual_out' => (float) $session->movements()->whereNull('payment_id')->where('type', CashMovementType::Cikis)->sum('amount'),
            'expected_cash' => $session->expected_amount !== null ? (float) $session->expected_amount : $this->expectedCash($session),
        ];
    }

    /**
     * Kasa açma/kapama aynı anda iki kez yapılmasın diye MySQL adlandırılmış kilidi kullanılır.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function withRegisterLock(callable $callback): mixed
    {
        $locked = (int) DB::scalar("SELECT GET_LOCK('demlio_kasa', 5)");

        if ($locked !== 1) {
            throw new DomainException('Kasa şu anda başka bir işlemde; lütfen tekrar deneyin.');
        }

        try {
            return DB::transaction($callback);
        } finally {
            DB::scalar("SELECT RELEASE_LOCK('demlio_kasa')");
        }
    }
}
