<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'action', 'subject_type', 'subject_id', 'description', 'properties'])]
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * İşlem geçmişi ekranındaki filtre grupları: işlem adı bu öneklerden biriyle başlıyorsa o gruptadır.
     *
     * @var array<string, array{label: string, icon: string, prefixes: array<int, string>}>
     */
    public const GROUPS = [
        'siparis' => ['label' => 'Sipariş', 'icon' => 'tabler-receipt', 'prefixes' => ['siparis_', 'kalem_']],
        'kasa' => ['label' => 'Kasa ve ödeme', 'icon' => 'tabler-cash-register', 'prefixes' => ['kasa_', 'odeme_']],
        'stok' => ['label' => 'Stok', 'icon' => 'tabler-package', 'prefixes' => ['stok_']],
        'menu' => ['label' => 'Menü', 'icon' => 'tabler-coffee', 'prefixes' => ['urun_', 'kategori_']],
        'masa' => ['label' => 'Masa', 'icon' => 'tabler-armchair', 'prefixes' => ['masa_']],
        'calisan' => ['label' => 'Çalışan', 'icon' => 'tabler-users', 'prefixes' => ['calisan_', 'profil_']],
        'oturum' => ['label' => 'Giriş / çıkış', 'icon' => 'tabler-login', 'prefixes' => ['giris', 'cikis']],
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * İşlemin ait olduğu grup anahtarı (GROUPS).
     */
    public function group(): ?string
    {
        foreach (self::GROUPS as $key => $group) {
            foreach ($group['prefixes'] as $prefix) {
                if (str_starts_with($this->action, $prefix)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function icon(): string
    {
        return self::GROUPS[$this->group()]['icon'] ?? 'tabler-point';
    }

    /**
     * Grup filtresi: işlem adı grubun öneklerinden biriyle başlayan kayıtlar.
     */
    public function scopeInGroup(Builder $query, string $group): void
    {
        $prefixes = self::GROUPS[$group]['prefixes'] ?? [];

        $query->where(function (Builder $q) use ($prefixes) {
            foreach ($prefixes as $prefix) {
                $q->orWhere('action', 'like', $prefix.'%');
            }
        });
    }

    /**
     * Arayüzde işlemi renklendirmek için Bootstrap renk adı döner.
     */
    public function color(): string
    {
        return match (true) {
            str_ends_with($this->action, '_eklendi'), str_starts_with($this->action, 'odeme_') => 'success',
            str_ends_with($this->action, '_silindi'), str_ends_with($this->action, '_iptal') => 'danger',
            str_starts_with($this->action, 'stok_') => 'warning',
            str_ends_with($this->action, '_guncellendi') => 'info',
            $this->action === 'giris' => 'primary',
            default => 'secondary',
        };
    }

    /**
     * İşlem geçmişine yeni bir kayıt ekler.
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(string $action, string $description, ?Model $subject = null, array $properties = []): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
        ]);
    }
}
