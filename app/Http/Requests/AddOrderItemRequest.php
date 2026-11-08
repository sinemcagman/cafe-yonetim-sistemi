<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddOrderItemRequest extends FormRequest
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
            'product_id' => ['required', Rule::exists('products', 'id')],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['quantity' => $this->input('quantity', 1)]);
    }

    public function attributes(): array
    {
        return ['product_id' => 'ürün', 'quantity' => 'adet'];
    }
}
