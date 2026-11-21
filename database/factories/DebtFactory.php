<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Debt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['dipinjam_orang', 'kita_pinjam']),
            'nama_orang' => fake()->name(),
            'nominal_total' => fake()->numberBetween(50000, 2000000),
            'account_id' => Account::factory(),
            'initial_transaction_id' => null,
            'tanggal' => now()->toDateString(),
            'jatuh_tempo' => now()->addDays(14)->toDateString(),
            'keterangan' => fake()->sentence(),
            'status' => 'belum_lunas',
        ];
    }
}
