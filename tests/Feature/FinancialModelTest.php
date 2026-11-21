<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

test('calculates account balance accurately for income, expense, and transfer', function () {
    $bca = Account::create(['nama' => 'BCA', 'saldo_awal' => 1000000]);
    $dompet = Account::create(['nama' => 'Dompet', 'saldo_awal' => 200000]);
    $gopay = Account::create(['nama' => 'GoPay', 'saldo_awal' => 50000]);

    $kategoriGaji = Category::create(['nama' => 'Gaji', 'tipe' => 'masuk']);
    $kategoriMakan = Category::create(['nama' => 'Makan', 'tipe' => 'keluar']);

    // 1. Gaji masuk ke BCA: +5.000.000 -> BCA jadi 6.000.000
    Transaction::create([
        'type' => 'masuk',
        'account_id' => $bca->id,
        'category_id' => $kategoriGaji->id,
        'nominal' => 5000000,
        'tanggal' => now()->toDateString(),
    ]);

    // 2. Belanja makan pakai Dompet: -50.000 -> Dompet jadi 150.000
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $dompet->id,
        'category_id' => $kategoriMakan->id,
        'nominal' => 50000,
        'tanggal' => now()->toDateString(),
    ]);

    // 3. Tarik tunai / Transfer BCA -> Dompet: 500.000
    // BCA: 6.000.000 - 500.000 = 5.500.000
    // Dompet: 150.000 + 500.000 = 650.000
    Transaction::create([
        'type' => 'transfer',
        'account_id' => $bca->id,
        'target_account_id' => $dompet->id,
        'nominal' => 500000,
        'tanggal' => now()->toDateString(),
    ]);

    // 4. Topup GoPay dari BCA: 200.000
    // BCA: 5.500.000 - 200.000 = 5.300.000
    // GoPay: 50.000 + 200.000 = 250.000
    Transaction::create([
        'type' => 'transfer',
        'account_id' => $bca->id,
        'target_account_id' => $gopay->id,
        'nominal' => 200000,
        'tanggal' => now()->toDateString(),
    ]);

    expect($bca->balance)->toBe(5300000)
        ->and($dompet->balance)->toBe(650000)
        ->and($gopay->balance)->toBe(250000);

    // Test withCalculatedBalance scope
    $accountsWithScope = Account::withCalculatedBalance()->get()->keyBy('nama');
    expect($accountsWithScope['BCA']->balance)->toBe(5300000)
        ->and($accountsWithScope['Dompet']->balance)->toBe(650000)
        ->and($accountsWithScope['GoPay']->balance)->toBe(250000);
});
