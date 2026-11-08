<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Kalem ya da sipariş iptali: iptal nedeni zorunludur ve işlem geçmişine yazılır.
 */
class CancelRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['reason' => 'iptal nedeni'];
    }
}
