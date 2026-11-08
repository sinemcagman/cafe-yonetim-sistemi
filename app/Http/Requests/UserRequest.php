<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Yeni çalışanda şifre zorunludur; düzenlemede boş bırakılırsa şifre değişmez.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9 +()-]{7,20}$/'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function attributes(): array
    {
        return [
            'name' => 'ad soyad',
            'email' => 'e-posta adresi',
            'phone' => 'telefon',
            'role' => 'rol',
            'password' => 'şifre',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Telefon numarası yalnızca rakam, boşluk ve + ( ) - içerebilir.',
        ];
    }
}
