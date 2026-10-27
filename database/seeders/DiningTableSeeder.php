<?php

namespace Database\Seeders;

use App\Enums\TableStatus;
use App\Models\DiningTable;
use Illuminate\Database\Seeder;

class DiningTableSeeder extends Seeder
{
    /**
     * Salon ve bahçe için numaralı masaları oluşturur.
     */
    public function run(): void
    {
        foreach (range(1, 12) as $number) {
            DiningTable::updateOrCreate(
                ['number' => $number],
                [
                    'area' => $number <= 8 ? 'Salon' : 'Bahçe',
                    'capacity' => $number % 3 === 0 ? 6 : 4,
                    'status' => TableStatus::Bos,
                    'is_active' => true,
                ],
            );
        }
    }
}
