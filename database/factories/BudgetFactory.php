<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory()->create(['tipe' => 'keluar'])->id,
            'nominal' => fake()->randomElement([500000, 1000000, 2000000, 3000000]),
            'start_month' => now()->format('Y-m'),
            'end_month' => null,
            'keterangan' => fake()->optional()->sentence(3),
        ];
    }
}
