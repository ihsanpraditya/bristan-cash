<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['nama' => 'BCA', 'saldo_awal' => 0, 'warna' => '#0066AE', 'icon' => 'credit-card'],
            ['nama' => 'GoPay', 'saldo_awal' => 0, 'warna' => '#00AED6', 'icon' => 'device-phone-mobile'],
            ['nama' => 'Dompet Tunai', 'saldo_awal' => 0, 'warna' => '#16A34A', 'icon' => 'banknotes'],
            ['nama' => 'ShopeePay', 'saldo_awal' => 0, 'warna' => '#EE4D2D', 'icon' => 'shopping-bag'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['nama' => $account['nama']], $account);
        }
    }
}
