<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['Belanja Dapur', 'Makan & Jajan', 'Tagihan & Listrik', 'Anak & Sekolah', 'Bensin & Transport']),
            'tipe' => 'keluar',
            'icon' => 'shopping-cart',
            'urutan' => fake()->numberBetween(1, 10),
        ];
    }
}
