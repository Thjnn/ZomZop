<?php

use App\Http\Controllers\CustomerOrderCancellationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/orders/{order}/cancel', [CustomerOrderCancellationController::class, 'create'])
        ->name('orders.cancel.create');
    Route::post('/orders/{order}/cancel', [CustomerOrderCancellationController::class, 'store'])
        ->name('orders.cancel.store');
});
