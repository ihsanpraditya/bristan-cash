<?php

use App\Livewire\FinanceTracker;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Livewire\Livewire;

beforeEach(function () {
    $this->account1 = Account::create(['nama' => 'BCA', 'saldo_awal' => 1000000]);
    $this->account2 = Account::create(['nama' => 'Dompet', 'saldo_awal' => 500000]);
    $this->catKeluar = Category::create(['nama' => 'Belanja', 'tipe' => 'keluar']);
    $this->catMasuk = Category::create(['nama' => 'Gaji', 'tipe' => 'masuk']);
});

it('renders successfully with accounts and categories', function () {
    Livewire::test(FinanceTracker::class)
        ->assertStatus(200)
        ->assertSee('Keuangan Keluarga')
        ->assertSee('BCA')
        ->assertSee('Dompet');
});

it('can record an expense transaction', function () {
    Livewire::test(FinanceTracker::class)
        ->call('openInput', 'keluar')
        ->set('account_id', $this->account1->id)
        ->set('category_id', $this->catKeluar->id)
        ->set('nominal', '75000')
        ->set('keterangan', 'Beli sayur')
        ->call('saveTransaction')
        ->assertHasNoErrors();

    expect(Transaction::count())->toBe(1);

    $trx = Transaction::first();
    expect($trx->type)->toBe('keluar')
        ->and($trx->nominal)->toBe(75000)
        ->and($trx->account_id)->toBe($this->account1->id)
        ->and($trx->category_id)->toBe($this->catKeluar->id)
        ->and($trx->keterangan)->toBe('Beli sayur');

    // Saldo BCA should be 1.000.000 - 75.000 = 925.000
    expect($this->account1->fresh()->balance)->toBe(925000);
});

it('can record an income transaction', function () {
    Livewire::test(FinanceTracker::class)
        ->call('openInput', 'masuk')
        ->set('account_id', $this->account1->id)
        ->set('category_id', $this->catMasuk->id)
        ->set('nominal', '3000000')
        ->call('saveTransaction')
        ->assertHasNoErrors();

    expect(Transaction::count())->toBe(1);
    expect($this->account1->fresh()->balance)->toBe(4000000);
});

it('can record a balance transfer between accounts', function () {
    Livewire::test(FinanceTracker::class)
        ->call('openInput', 'transfer')
        ->set('account_id', $this->account1->id)
        ->set('target_account_id', $this->account2->id)
        ->set('nominal', '200000')
        ->call('saveTransaction')
        ->assertHasNoErrors();

    expect(Transaction::count())->toBe(1);

    // BCA: 1.000.000 - 200.000 = 800.000
    // Dompet: 500.000 + 200.000 = 700.000
    expect($this->account1->fresh()->balance)->toBe(800000)
        ->and($this->account2->fresh()->balance)->toBe(700000);
});

it('prevents transfer to the same account', function () {
    Livewire::test(FinanceTracker::class)
        ->call('openInput', 'transfer')
        ->set('account_id', $this->account1->id)
        ->set('target_account_id', $this->account1->id)
        ->set('nominal', '50000')
        ->call('saveTransaction')
        ->assertHasErrors(['target_account_id']);
});

it('can edit an existing transaction', function () {
    $trx = Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account1->id,
        'category_id' => $this->catKeluar->id,
        'nominal' => 50000,
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::test(FinanceTracker::class)
        ->call('editTransaction', $trx->id)
        ->set('nominal', '90000')
        ->call('saveTransaction')
        ->assertHasNoErrors();

    expect($trx->fresh()->nominal)->toBe(90000);
});

it('can delete a transaction', function () {
    $trx = Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account1->id,
        'category_id' => $this->catKeluar->id,
        'nominal' => 50000,
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::test(FinanceTracker::class)
        ->call('confirmDelete', $trx->id)
        ->call('deleteTransaction');

    expect(Transaction::count())->toBe(0);
});

it('supports batch syncing offline transactions', function () {
    $items = [
        [
            'type' => 'keluar',
            'account_id' => $this->account1->id,
            'category_id' => $this->catKeluar->id,
            'nominal' => 30000,
            'keterangan' => 'Jajan pasar',
            'tanggal' => now()->toDateString(),
        ],
        [
            'type' => 'masuk',
            'account_id' => $this->account2->id,
            'category_id' => $this->catMasuk->id,
            'nominal' => 100000,
            'keterangan' => 'Kembalian belanja',
            'tanggal' => now()->toDateString(),
        ],
    ];

    $component = Livewire::test(FinanceTracker::class);
    $synced = $component->instance()->syncBatch($items);

    expect($synced)->toBe(2)
        ->and(Transaction::count())->toBe(2);
});

it('can stream CSV export', function () {
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account1->id,
        'category_id' => $this->catKeluar->id,
        'nominal' => 50000,
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::test(FinanceTracker::class)
        ->call('exportCsv')
        ->assertFileDownloaded('keuangan-keluarga-'.now()->format('Y-m-d').'.csv');
});
