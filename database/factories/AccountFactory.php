<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement(['BCA', 'GoPay', 'Dompet Tunai', 'Mandiri', 'ShopeePay']),
            'saldo_awal' => fake()->numberBetween(100000, 5000000),
            'warna' => fake()->hexColor(),
            'icon' => 'wallet',
        ];
    }
}
