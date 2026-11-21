<?php

use App\Livewire\AccountManager;
use App\Livewire\CategoryManager;
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
});

require __DIR__.'/settings.php';
