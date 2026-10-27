<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ürün adedi yalnızca yeni ürün eklenirken girilir; sonraki değişiklikler
     * stok hareketi olarak kaydedilmek üzere "Stok Güncelle" ile yapılır.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'name' => [
                'required', 'string', 'max:150',
                Rule::unique('products')->where('category_id', $this->input('category_id'))->ignore($product),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999', 'decimal:0,2'],
            'stock_quantity' => [$product ? 'exclude' : 'required', 'integer', 'min:0', 'max:1000000'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $price = trim((string) $this->input('price'));

        // Türkçe yazım: "1.250,50" → "1250.50"
        if (str_contains($price, ',')) {
            $price = str_replace(['.', ','], ['', '.'], $price);
        }

        $this->merge([
            'price' => $price,
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
