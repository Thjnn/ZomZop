<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CouponSendController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ManagerAccountController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\MenuItemImageController;
use Illuminate\Support\Facades\Route;

// Khu vực quản trị toàn chuỗi. Các phase sau chỉ THÊM route vào nhóm này.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::patch('/branches/{branch}/toggle', [BranchController::class, 'toggle'])->name('branches.toggle');

        Route::get('/managers', [ManagerAccountController::class, 'index'])->name('managers.index');
        Route::get('/managers/create', [ManagerAccountController::class, 'create'])->name('managers.create');
        Route::post('/managers', [ManagerAccountController::class, 'store'])->name('managers.store');
        Route::get('/managers/{user}/edit', [ManagerAccountController::class, 'edit'])->name('managers.edit');
        Route::put('/managers/{user}', [ManagerAccountController::class, 'update'])->name('managers.update');
        Route::patch('/managers/{user}/lock', [ManagerAccountController::class, 'toggleLock'])->name('managers.lock');
        Route::put('/managers/{user}/password', [ManagerAccountController::class, 'resetPassword'])->name('managers.password');

        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');

        Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
        Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
        Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
        Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
        Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');

        Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
        Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
        Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
        Route::patch('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('/coupons/{coupon}/send', [CouponSendController::class, 'create'])->name('coupons.send.create');
        Route::post('/coupons/{coupon}/send', [CouponSendController::class, 'store'])->name('coupons.send.store');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

        Route::get('/menu-items', [MenuItemController::class, 'index'])->name('menu-items.index');
        Route::get('/menu-items/create', [MenuItemController::class, 'create'])->name('menu-items.create');
        Route::post('/menu-items', [MenuItemController::class, 'store'])->name('menu-items.store');
        Route::get('/menu-items/{menuItem}/edit', [MenuItemController::class, 'edit'])->name('menu-items.edit');
        Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update'])->name('menu-items.update');
        Route::patch('/menu-items/{menuItem}/toggle', [MenuItemController::class, 'toggle'])->name('menu-items.toggle');

        Route::get('/menu-items/{menuItem}/images', [MenuItemImageController::class, 'index'])->name('menu-items.images');
        Route::post('/menu-items/{menuItem}/images', [MenuItemImageController::class, 'store'])->name('menu-items.images.store');
        Route::scopeBindings()->group(function () {
            Route::patch('/menu-items/{menuItem}/images/{image}/primary', [MenuItemImageController::class, 'primary'])->name('menu-items.images.primary');
            Route::delete('/menu-items/{menuItem}/images/{image}', [MenuItemImageController::class, 'destroy'])->name('menu-items.images.destroy');
        });

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
        Route::patch('/customers/{user}/lock', [CustomerController::class, 'toggleLock'])->name('customers.lock');
    });
