<?php

use App\Livewire\BudgetManager;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->catMakan = Category::create([
        'nama' => 'Makan & Jajan',
        'tipe' => 'keluar',
        'icon' => 'sparkles',
        'urutan' => 1,
    ]);
    $this->catDapur = Category::create([
        'nama' => 'Belanja Dapur',
        'tipe' => 'keluar',
        'icon' => 'shopping-cart',
        'urutan' => 2,
    ]);
});

it('redirects guests from budgets page to login', function () {
    $this->get(route('budgets.index'))
        ->assertRedirect(route('login'));
});

it('renders successfully for authenticated users', function () {
    $this->actingAs($this->user);

    $this->get(route('budgets.index'))
        ->assertOk()
        ->assertSee('Anggaran Belanja Bulanan')
        ->assertSee('Makan & Jajan');
});

it('can create a recurring monthly budget starting from current month', function () {
    $currentMonth = now()->format('Y-m');

    Livewire::actingAs($this->user)
        ->test(BudgetManager::class)
        ->call('openCreate', $this->catMakan->id)
        ->set('category_id', $this->catMakan->id)
        ->set('nominal', '2.000.000')
        ->set('durationType', 'recurring')
        ->set('start_month', $currentMonth)
        ->call('save')
        ->assertHasNoErrors();

    expect(Budget::where('category_id', $this->catMakan->id)->exists())->toBeTrue();

    $budget = Budget::where('category_id', $this->catMakan->id)->first();
    expect($budget->nominal)->toBe(2000000)
        ->and($budget->start_month)->toBe($currentMonth)
        ->and($budget->end_month)->toBeNull();
});

it('can create a customized budget for a single specific month', function () {
    $targetMonth = now()->addMonths(2)->format('Y-m');

    Livewire::actingAs($this->user)
        ->test(BudgetManager::class)
        ->set('selectedMonth', $targetMonth)
        ->call('openCreate', $this->catDapur->id)
        ->set('category_id', $this->catDapur->id)
        ->set('nominal', '3.500.000')
        ->set('durationType', 'specific_month')
        ->set('start_month', $targetMonth)
        ->call('save')
        ->assertHasNoErrors();

    $budget = Budget::where('category_id', $this->catDapur->id)->first();
    expect($budget->nominal)->toBe(3500000)
        ->and($budget->start_month)->toBe($targetMonth)
        ->and($budget->end_month)->toBe($targetMonth);
});

it('prioritizes specific month budget over recurring budget in resolution', function () {
    $currentMonth = now()->format('Y-m');

    // 1. Recurring budget: Rp 1.500.000 mulai sekarang
    Budget::create([
        'category_id' => $this->catMakan->id,
        'nominal' => 1500000,
        'start_month' => $currentMonth,
        'end_month' => null,
    ]);

    // 2. Specific budget for next month: Rp 2.500.000 (misal liburan)
    $nextMonth = now()->addMonth()->format('Y-m');
    Budget::create([
        'category_id' => $this->catMakan->id,
        'nominal' => 2500000,
        'start_month' => $nextMonth,
        'end_month' => $nextMonth,
    ]);

    // Month in 2 months (recurring budget applies)
    $twoMonthsLater = now()->addMonths(2)->format('Y-m');

    expect(Budget::forCategoryAndMonth($this->catMakan->id, $currentMonth)->nominal)->toBe(1500000)
        ->and(Budget::forCategoryAndMonth($this->catMakan->id, $nextMonth)->nominal)->toBe(2500000)
        ->and(Budget::forCategoryAndMonth($this->catMakan->id, $twoMonthsLater)->nominal)->toBe(1500000);
});

it('calculates remaining budget and spent percentage correctly with actual transactions', function () {
    $currentMonth = now()->format('Y-m');
    $account = Account::create(['nama' => 'BCA', 'saldo_awal' => 5000000]);

    // Budget: 1.000.000
    Budget::create([
        'category_id' => $this->catMakan->id,
        'nominal' => 1000000,
        'start_month' => $currentMonth,
        'end_month' => null,
    ]);

    // Expense: 400.000 this month
    Transaction::create([
        'type' => 'keluar',
        'account_id' => $account->id,
        'category_id' => $this->catMakan->id,
        'nominal' => 400000,
        'tanggal' => now()->toDateString(),
    ]);

    Livewire::actingAs($this->user)
        ->test(BudgetManager::class)
        ->set('selectedMonth', $currentMonth)
        ->assertSee('Rp 1.000.000') // Limit
        ->assertSee('Rp 400.000')   // Spent
        ->assertSee('Sisa Rp 600.000'); // Remaining
});

it('can navigate between months', function () {
    $thisMonth = now()->format('Y-m');
    $prevMonth = now()->subMonth()->format('Y-m');
    $nextMonth = now()->addMonth()->format('Y-m');

    Livewire::actingAs($this->user)
        ->test(BudgetManager::class)
        ->assertSet('selectedMonth', $thisMonth)
        ->call('previousMonth')
        ->assertSet('selectedMonth', $prevMonth)
        ->call('nextMonth')
        ->assertSet('selectedMonth', $thisMonth)
        ->call('nextMonth')
        ->assertSet('selectedMonth', $nextMonth)
        ->call('setThisMonth')
        ->assertSet('selectedMonth', $thisMonth);
});

it('can edit and delete a budget', function () {
    $budget = Budget::create([
        'category_id' => $this->catMakan->id,
        'nominal' => 1000000,
        'start_month' => now()->format('Y-m'),
        'end_month' => null,
    ]);

    Livewire::actingAs($this->user)
        ->test(BudgetManager::class)
        ->call('edit', $budget->id)
        ->set('nominal', '1.800.000')
        ->call('save')
        ->assertHasNoErrors();

    expect($budget->fresh()->nominal)->toBe(1800000);

    Livewire::actingAs($this->user)
        ->test(BudgetManager::class)
        ->call('confirmDelete', $budget->id)
        ->call('deleteBudget');

    expect(Budget::find($budget->id))->toBeNull();
});
