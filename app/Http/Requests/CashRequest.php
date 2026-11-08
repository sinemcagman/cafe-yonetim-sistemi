<?php

namespace App\Http\Requests;

use App\Enums\CashMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Kasa açma, kasa hareketi ve kasa kapama formları. Hangi alanların zorunlu olduğu route adına göre belirlenir.
 */
class CashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $amount = ['required', 'numeric', 'min:0', 'max:10000000', 'decimal:0,2'];

        return match ($this->route()->getName()) {
            'cash.open' => [
                'opening_amount' => $amount,
                'note' => ['nullable', 'string', 'max:500'],
            ],
            'cash.movement' => [
                'type' => ['required', Rule::enum(CashMovementType::class)],
                'amount' => [...$amount, 'gt:0'],
                'description' => ['required', 'string', 'max:255'],
            ],
            'cash.close' => [
                'closing_amount' => $amount,
                'note' => ['nullable', 'string', 'max:500'],
            ],
            default => [],
        };
    }

    protected function prepareForValidation(): void
    {
        foreach (['opening_amount', 'amount', 'closing_amount'] as $field) {
            if ($this->has($field)) {
                $this->merge([$field => normalize_amount($this->input($field))]);
            }
        }
    }

    public function attributes(): array
    {
        return [
            'opening_amount' => 'başlangıç nakdi',
            'closing_amount' => 'sayılan nakit',
            'amount' => 'tutar',
            'type' => 'hareket türü',
            'description' => 'açıklama',
            'note' => 'not',
        ];
    }
}
