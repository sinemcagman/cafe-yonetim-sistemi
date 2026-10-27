<?php

namespace App\Http\Requests;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Stok girişinde adet pozitif olmalıdır; düzeltmede (fire, sayım farkı)
     * eksi değer girilerek stok azaltılabilir.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([StockMovementType::Giris->value, StockMovementType::Duzeltme->value])],
            'quantity' => [
                'required', 'integer', 'not_in:0', 'max:100000',
                $this->input('type') === StockMovementType::Giris->value ? 'min:1' : 'min:-100000',
            ],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['type' => 'hareket türü'];
    }
}
