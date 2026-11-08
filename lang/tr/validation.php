<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Projede kullanılan doğrulama kurallarının Türkçe karşılıkları. Burada
    | bulunmayan kurallar için lang/en/validation.php dosyası kullanılır.
    |
    */

    'accepted' => ':attribute kabul edilmelidir.',
    'array' => ':attribute bir dizi olmalıdır.',
    'between' => [
        'numeric' => ':attribute :min ile :max arasında olmalıdır.',
        'string' => ':attribute :min ile :max karakter arasında olmalıdır.',
    ],
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'confirmed' => ':attribute tekrarı eşleşmiyor.',
    'current_password' => 'Şifre hatalı.',
    'different' => ':attribute ile :other farklı olmalıdır.',
    'decimal' => ':attribute için virgülden sonra en fazla iki basamak girilebilir.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'in' => 'Seçilen :attribute geçersiz.',
    'integer' => ':attribute bir tam sayı olmalıdır.',
    'max' => [
        'numeric' => ':attribute :max değerinden büyük olamaz.',
        'string' => ':attribute :max karakterden uzun olamaz.',
    ],
    'min' => [
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter olmalıdır.',
    ],
    'not_in' => 'Seçilen :attribute geçersiz.',
    'numeric' => ':attribute bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute en az bir harf içermelidir.',
        'mixed' => ':attribute en az bir büyük ve bir küçük harf içermelidir.',
        'numbers' => ':attribute en az bir rakam içermelidir.',
        'symbols' => ':attribute en az bir sembol içermelidir.',
        'uncompromised' => 'Bu :attribute bir veri sızıntısında görülmüş; lütfen farklı bir :attribute seçin.',
    ],
    'regex' => ':attribute biçimi geçersiz.',
    'required' => ':attribute alanı zorunludur.',
    'required_if' => ':other :value olduğunda :attribute alanı zorunludur.',
    'string' => ':attribute bir metin olmalıdır.',
    'unique' => 'Bu :attribute zaten kullanılıyor.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'ad',
        'email' => 'e-posta adresi',
        'password' => 'şifre',
        'phone' => 'telefon',
        'role' => 'rol',
        'icon' => 'ikon',
        'sort_order' => 'sıra',
        'category_id' => 'kategori',
        'description' => 'açıklama',
        'price' => 'satış fiyatı',
        'stock_quantity' => 'ürün adedi',
        'low_stock_threshold' => 'kritik stok seviyesi',
        'quantity' => 'adet',
        'reason' => 'neden',
        'number' => 'masa numarası',
        'area' => 'bölüm',
        'capacity' => 'kapasite',
    ],

];
