<?php

use App\Livewire\ExpenseReport;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->account = Account::create([
        'nama' => 'BCA Rekening Utama',
        'saldo_awal' => 10000000,
        'warna' => '#3b82f6',
        'icon' => 'building-library',
    ]);

    $this->catDapur = Category::create([
        'nama' => 'Belanja Dapur',
        'tipe' => 'keluar',
        'icon' => 'shopping-cart',
        'urutan' => 1,
    ]);

    $this->catMakan = Category::create([
        'nama' => 'Makan & Jajan',
        'tipe' => 'keluar',
        'icon' => 'sparkles',
        'urutan' => 2,
    ]);
});

it('redirects guests from expense analysis page to login', function () {
    $this->get(route('expenses.analysis'))
        ->assertRedirect(route('login'));
});

it('renders expense analysis successfully for authenticated users', function () {
    $this->actingAs($this->user);

    $this->get(route('expenses.analysis'))
        ->assertOk()
        ->assertSee('Analisis Pos Belanja')
        ->assertSee('Komposisi Pengeluaran');
});

it('calculates total expenses and category breakdown accurately', function () {
    // Create transactions for this month
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $this->catDapur->id,
        'nominal' => 300000,
        'keterangan' => 'Belanja sayur & daging',
        'tanggal' => now()->toDateString(),
    ]);

    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $this->catMakan->id,
        'nominal' => 100000,
        'keterangan' => 'Beli kopi sore',
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::actingAs($this->user)
        ->test(ExpenseReport::class)
        ->assertSee('Rp 400.000') // Total expenses: 300k + 100k
        ->assertSee('Belanja Dapur')
        ->assertSee('Makan & Jajan')
        ->assertSee('75%') // 300k / 400k = 75%
        ->assertSee('25%') // 100k / 400k = 25%
        ->assertSee('Belanja sayur & daging')
        ->assertSee('Beli kopi sore');
});

it('can filter transactions by selecting a category and clear filter', function () {
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $this->catDapur->id,
        'nominal' => 250000,
        'keterangan' => 'Belanja mingguan pasar',
        'tanggal' => now()->toDateString(),
    ]);

    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $this->catMakan->id,
        'nominal' => 75000,
        'keterangan' => 'Makan mie ayam',
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::actingAs($this->user)
        ->test(ExpenseReport::class)
        // Default: both transactions visible
        ->assertSee('Belanja mingguan pasar')
        ->assertSee('Makan mie ayam')
        // Select 'Belanja Dapur'
        ->call('selectCategory', $this->catDapur->id)
        ->assertSet('selectedCategoryId', $this->catDapur->id)
        ->assertSee('Belanja mingguan pasar')
        ->assertDontSee('Makan mie ayam')
        // Clear filter
        ->call('clearCategory')
        ->assertSet('selectedCategoryId', null)
        ->assertSee('Belanja mingguan pasar')
        ->assertSee('Makan mie ayam');
});

it('can switch time periods correctly', function () {
    // Transaction this month
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $this->catDapur->id,
        'nominal' => 150000,
        'keterangan' => 'Belanja bulan ini',
        'tanggal' => now()->toDateString(),
    ]);

    // Transaction last month
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $this->catDapur->id,
        'nominal' => 500000,
        'keterangan' => 'Belanja bulan kemarin',
        'tanggal' => now()->subMonth()->startOfMonth()->toDateString(),
    ]);

    Livewire::actingAs($this->user)
        ->test(ExpenseReport::class)
        ->assertSet('period', 'bulan_ini')
        ->assertSee('Belanja bulan ini')
        ->assertDontSee('Belanja bulan kemarin')
        ->call('setPeriod', 'bulan_lalu')
        ->assertSet('period', 'bulan_lalu')
        ->assertSee('Belanja bulan kemarin')
        ->assertDontSee('Belanja bulan ini');
});
