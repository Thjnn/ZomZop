<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

Route::get('/branches/select', [BranchController::class, 'select'])->name('branches.select');
Route::post('/branches/confirm', [BranchController::class, 'confirm'])->name('branches.confirm');
Route::get('/menu-items/{id}/detail', [App\Http\Controllers\MenuItemController::class, 'detail'])->name('menu.detail');

// Cart — không yêu cầu auth, guest có thể thêm vào giỏ
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::middleware('auth')->group(function () {
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::get('/favorites/ids', [FavoriteController::class, 'ids'])->name('favorites.ids');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/order/{orderCode}', [CheckoutController::class, 'success'])->name('order.success');
});
