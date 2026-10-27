<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiningTable>
 */
class DiningTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(1, 500),
            'area' => 'Salon',
            'capacity' => 4,
            'status' => TableStatus::Bos,
            'is_active' => true,
        ];
    }
}
