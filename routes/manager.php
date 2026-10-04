<?php

use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    });
