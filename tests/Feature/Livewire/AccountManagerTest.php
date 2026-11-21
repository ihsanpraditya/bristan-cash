<?php

use App\Livewire\AccountManager;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->account = Account::create([
        'nama' => 'BCA',
        'saldo_awal' => 1000000,
        'warna' => '#0066AE',
        'icon' => 'credit-card',
    ]);
});

it('redirects guests from accounts page to login', function () {
    $this->get(route('accounts.index'))
        ->assertRedirect(route('login'));
});

it('renders successfully for authenticated users', function () {
    $this->actingAs($this->user);

    $this->get(route('accounts.index'))
        ->assertOk()
        ->assertSee('BCA')
        ->assertSee('Tambah Dompet Baru');
});

it('can create a new account with thousand separator in initial balance', function () {
    Livewire::actingAs($this->user)
        ->test(AccountManager::class)
        ->call('openCreate')
        ->set('nama', 'Mandiri Tabungan')
        ->set('saldo_awal', '2.500.000')
        ->set('warna', '#003D79')
        ->set('icon', 'building-library')
        ->call('save')
        ->assertHasNoErrors();

    expect(Account::where('nama', 'Mandiri Tabungan')->exists())->toBeTrue();

    $newAcc = Account::where('nama', 'Mandiri Tabungan')->first();
    expect($newAcc->saldo_awal)->toBe(2500000)
        ->and($newAcc->warna)->toBe('#003D79')
        ->and($newAcc->icon)->toBe('building-library');
});

it('can edit an existing account', function () {
    Livewire::actingAs($this->user)
        ->test(AccountManager::class)
        ->call('edit', $this->account->id)
        ->assertSet('nama', 'BCA')
        ->assertSet('saldo_awal', '1.000.000')
        ->set('nama', 'BCA Payroll')
        ->set('saldo_awal', '1.500.000')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->account->fresh()->nama)->toBe('BCA Payroll')
        ->and($this->account->fresh()->saldo_awal)->toBe(1500000);
});

it('prevents deleting an account that has transactions', function () {
    $category = Category::create(['nama' => 'Belanja', 'tipe' => 'keluar']);

    Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->account->id,
        'category_id' => $category->id,
        'nominal' => 50000,
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::actingAs($this->user)
        ->test(AccountManager::class)
        ->call('confirmDelete', $this->account->id)
        ->assertSee('tidak bisa dihapus langsung')
        ->call('deleteAccount');

    // Account still exists
    expect(Account::find($this->account->id))->not->toBeNull();
});

it('can delete an account that has no transactions', function () {
    $emptyAccount = Account::create([
        'nama' => 'Dompet Cadangan',
        'saldo_awal' => 0,
        'warna' => '#64748B',
        'icon' => 'wallet',
    ]);

    Livewire::actingAs($this->user)
        ->test(AccountManager::class)
        ->call('confirmDelete', $emptyAccount->id)
        ->assertSet('deleteErrorMessage', null)
        ->call('deleteAccount');

    expect(Account::find($emptyAccount->id))->toBeNull();
});
