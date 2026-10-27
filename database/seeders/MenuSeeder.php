<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * README'deki örnek menüyü (İçecekler, Yiyecekler...) oluşturur.
     */
    public function run(): void
    {
        $menu = [
            'Sıcak İçecekler' => [
                'icon' => 'fi fi-rr-mug-hot-alt',
                'products' => [
                    ['Çay', 15, 200],
                    ['Fincan Çay', 25, 150],
                    ['Türk Kahvesi', 60, 80],
                    ['Americano', 75, 60],
                    ['Latte', 85, 60],
                    ['Sıcak Çikolata', 80, 40],
                ],
            ],
            'Soğuk İçecekler' => [
                'icon' => 'fi fi-rr-glass',
                'products' => [
                    ['Su', 10, 120],
                    ['Ayran', 25, 50],
                    ['Limonata', 60, 30],
                    ['Ice Latte', 90, 40],
                    ['Soda', 20, 60],
                ],
            ],
            'Yiyecekler' => [
                'icon' => 'fi fi-rr-hamburger-soda',
                'products' => [
                    ['Kaşarlı Tost', 80, 40],
                    ['Karışık Tost', 100, 35],
                    ['Simit', 20, 50],
                    ['Börek', 70, 20],
                    ['Kahvaltı Tabağı', 220, 15],
                ],
            ],
            'Tatlılar' => [
                'icon' => 'fi fi-rr-cake-birthday',
                'products' => [
                    ['Cheesecake', 110, 12],
                    ['Brownie', 90, 15],
                    ['Sufle', 105, 4],
                ],
            ],
        ];

        $order = 0;

        foreach ($menu as $categoryName => $data) {
            $category = Category::updateOrCreate(
                ['name' => $categoryName],
                ['icon' => $data['icon'], 'sort_order' => $order++, 'is_active' => true],
            );

            foreach ($data['products'] as [$name, $price, $stock]) {
                $category->products()->updateOrCreate(
                    ['name' => $name],
                    ['price' => $price, 'stock_quantity' => $stock, 'low_stock_threshold' => 5, 'is_active' => true],
                );
            }
        }
    }
}
