<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'keluar',
            'account_id' => Account::factory(),
            'target_account_id' => null,
            'category_id' => Category::factory(),
            'nominal' => fake()->numberBetween(10000, 250000),
            'keterangan' => fake()->optional()->sentence(3),
            'tanggal' => now()->toDateString(),
        ];
    }
}
