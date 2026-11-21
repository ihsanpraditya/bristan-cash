<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DebtPayment>
 */
class DebtPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'debt_id' => Debt::factory(),
            'account_id' => Account::factory(),
            'transaction_id' => null,
            'nominal' => fake()->numberBetween(10000, 500000),
            'tanggal' => now()->toDateString(),
            'catatan' => fake()->sentence(),
        ];
    }
}
