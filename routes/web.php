<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])
    ->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ExpenseController::class, 'index'])
        ->name('expenses.index');

    Route::get('/daily', [ExpenseController::class, 'daily'])
        ->name('expenses.daily');

    Route::post('/budget', [ExpenseController::class, 'storeBudget'])
        ->name('budget.store');

    Route::post('/expenses', [ExpenseController::class, 'storeExpense'])
        ->name('expenses.store');

    Route::get('/expenses/export', [ExpenseController::class, 'export'])
        ->name('expenses.export');

    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->name('expenses.edit');

    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->name('expenses.update');

    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->name('expenses.destroy');

    Route::post('/incomes', [IncomeController::class, 'store'])
        ->name('incomes.store');

    Route::get('/incomes/export', [IncomeController::class, 'export'])
        ->name('incomes.export');

    Route::get('/incomes/{income}/edit', [IncomeController::class, 'edit'])
        ->name('incomes.edit');

    Route::put('/incomes/{income}', [IncomeController::class, 'update'])
        ->name('incomes.update');

    Route::delete('/incomes/{income}', [IncomeController::class, 'destroy'])
        ->name('incomes.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');
});