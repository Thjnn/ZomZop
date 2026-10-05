<?php

use App\Http\Controllers\Manager\AttendanceController;
use App\Http\Controllers\Manager\DashboardController;
use App\Http\Controllers\Manager\MenuController;
use App\Http\Controllers\Manager\OrderController;
use App\Http\Controllers\Manager\PayrollController;
use App\Http\Controllers\Manager\ReportController;
use App\Http\Controllers\Manager\ReviewController;
use App\Http\Controllers\Manager\ShiftController;
use App\Http\Controllers\Manager\StaffController;
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

        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{user}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
        Route::patch('/staff/{user}/lock', [StaffController::class, 'toggleLock'])->name('staff.lock');
        Route::put('/staff/{user}/password', [StaffController::class, 'resetPassword'])->name('staff.password');

        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');
        Route::patch('/attendances/{attendance}/checkout', [AttendanceController::class, 'checkout'])->name('attendances.checkout');

        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::post('/payrolls/recalculate', [PayrollController::class, 'recalculate'])->name('payrolls.recalculate');
        Route::put('/payrolls/{payroll}', [PayrollController::class, 'update'])->name('payrolls.update');
        Route::patch('/payrolls/{payroll}/confirm', [PayrollController::class, 'confirm'])->name('payrolls.confirm');
        Route::patch('/payrolls/{payroll}/pay', [PayrollController::class, 'pay'])->name('payrolls.pay');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    });
