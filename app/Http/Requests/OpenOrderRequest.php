<?php

namespace App\Http\Requests;

use App\Enums\OrderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenOrderRequest extends FormRequest
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
            'type' => ['required', Rule::enum(OrderType::class)],
            'dining_table_id' => [
                Rule::requiredIf($this->input('type') === OrderType::Masa->value),
                'nullable',
                Rule::exists('dining_tables', 'id')->where('is_active', true),
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'sipariş türü',
            'dining_table_id' => 'masa',
            'note' => 'sipariş notu',
        ];
    }
}
