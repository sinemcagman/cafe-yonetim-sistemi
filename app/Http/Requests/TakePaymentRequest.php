<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TakePaymentRequest extends FormRequest
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
        return [
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000', 'decimal:0,2'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['amount' => normalize_amount($this->input('amount'))]);
    }

    public function attributes(): array
    {
        return ['method' => 'ödeme yöntemi', 'amount' => 'tutar'];
    }
}
