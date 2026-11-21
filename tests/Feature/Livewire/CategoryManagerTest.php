<?php

use App\Livewire\CategoryManager;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->catKeluar = Category::create([
        'nama' => 'Belanja Dapur',
        'tipe' => 'keluar',
        'icon' => 'shopping-cart',
        'urutan' => 1,
    ]);
    $this->catMasuk = Category::create([
        'nama' => 'Gaji Pokok',
        'tipe' => 'masuk',
        'icon' => 'banknotes',
        'urutan' => 1,
    ]);
});

it('redirects guests from categories page to login', function () {
    $this->get(route('categories.index'))
        ->assertRedirect(route('login'));
});

it('renders successfully for authenticated users', function () {
    $this->actingAs($this->user);

    $this->get(route('categories.index'))
        ->assertOk()
        ->assertSee('Kategori Catatan Keuangan')
        ->assertSee('Belanja Dapur');
});

it('can switch tabs between expense and income categories', function () {
    Livewire::actingAs($this->user)
        ->test(CategoryManager::class)
        ->assertSet('activeTab', 'keluar')
        ->assertSee('Belanja Dapur')
        ->assertDontSee('Gaji Pokok')
        ->call('setTab', 'masuk')
        ->assertSet('activeTab', 'masuk')
        ->assertSee('Gaji Pokok')
        ->assertDontSee('Belanja Dapur');
});

it('can create a new expense category', function () {
    Livewire::actingAs($this->user)
        ->test(CategoryManager::class)
        ->call('openCreate', 'keluar')
        ->set('nama', 'Skincare & Perawatan')
        ->set('tipe', 'keluar')
        ->set('icon', 'sparkles')
        ->set('urutan', 2)
        ->call('save')
        ->assertHasNoErrors();

    expect(Category::where('nama', 'Skincare & Perawatan')->exists())->toBeTrue();

    $cat = Category::where('nama', 'Skincare & Perawatan')->first();
    expect($cat->tipe)->toBe('keluar')
        ->and($cat->icon)->toBe('sparkles');
});

it('can edit an existing category', function () {
    Livewire::actingAs($this->user)
        ->test(CategoryManager::class)
        ->call('edit', $this->catKeluar->id)
        ->assertSet('nama', 'Belanja Dapur')
        ->set('nama', 'Belanja Mingguan')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->catKeluar->fresh()->nama)->toBe('Belanja Mingguan');
});

it('prevents deleting a category that has transactions', function () {
    $account = Account::create(['nama' => 'Dompet', 'saldo_awal' => 100000]);

    Transaction::create([
        'type' => 'keluar',
        'account_id' => $account->id,
        'category_id' => $this->catKeluar->id,
        'nominal' => 25000,
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::actingAs($this->user)
        ->test(CategoryManager::class)
        ->call('confirmDelete', $this->catKeluar->id)
        ->assertSee('tidak bisa dihapus langsung')
        ->call('deleteCategory');

    // Category still exists
    expect(Category::find($this->catKeluar->id))->not->toBeNull();
});

it('can delete a category that has no transactions', function () {
    $emptyCat = Category::create([
        'nama' => 'Kategori Sementara',
        'tipe' => 'keluar',
        'icon' => 'ellipsis-horizontal-circle',
        'urutan' => 99,
    ]);

    Livewire::actingAs($this->user)
        ->test(CategoryManager::class)
        ->call('confirmDelete', $emptyCat->id)
        ->assertSet('deleteErrorMessage', null)
        ->call('deleteCategory');

    expect(Category::find($emptyCat->id))->toBeNull();
});
