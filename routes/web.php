<?php

use App\Livewire\AccountManager;
use App\Livewire\BudgetManager;
use App\Livewire\CategoryManager;
use App\Livewire\DebtTracker;
use App\Livewire\ExpenseReport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('accounts', AccountManager::class)->name('accounts.index');
    Route::get('categories', CategoryManager::class)->name('categories.index');
    Route::get('expenses/analysis', ExpenseReport::class)->name('expenses.analysis');
    Route::get('budgets', BudgetManager::class)->name('budgets.index');
    Route::get('debts', DebtTracker::class)->name('debts.index');
});

require __DIR__.'/settings.php';
