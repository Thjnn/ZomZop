<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;

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

Route::get('/menu', function () {
    return view('account.menu');
});

// Các trang thông tin & chính sách
Route::get('/notifications', function () { return view('notifications.index'); })->name('notifications');
Route::get('/coupons', function () { return view('coupons.index'); })->name('coupons');
Route::get('/about-us', function () { return view('pages.about-us'); })->name('about-us');
Route::get('/support', function () { return view('pages.support'); })->name('support');
Route::get('/privacy-policy', function () { return view('pages.privacy-policy'); })->name('privacy-policy');

// Các trang hồ sơ, đơn hàng, địa chỉ (yêu thích đã có ở trên)
Route::middleware('auth')->group(function () {
    // Hồ sơ
    Route::get('/profile',          [AccountController::class, 'profile'])->name('account.profile');
    Route::put('/profile',          [AccountController::class, 'updateProfile'])->name('account.update');
    Route::put('/profile/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::delete('/profile',       [AccountController::class, 'destroy'])->name('account.destroy');

    // Đơn hàng (đang là dữ liệu mẫu)
    Route::view('/orders', 'account.orders')->name('orders');

    // Địa chỉ
    Route::get('/addresses',                    [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses',                   [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}',          [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}',       [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
});