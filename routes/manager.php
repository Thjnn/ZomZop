<?php

use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\MenuController;
use App\Http\Controllers\Manager\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
        Route::put('/menu/{menuItem}', [MenuController::class, 'update'])->name('menu.update');
    });
