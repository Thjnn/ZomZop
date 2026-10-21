<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('/notifications/read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::post('/notifications/{source}/{sourceId}/open', [NotificationController::class, 'open'])
        ->whereIn('source', ['order', 'coupon'])->whereNumber('sourceId')->name('notifications.open');
    Route::delete('/notifications/{source}/{sourceId}', [NotificationController::class, 'destroy'])
        ->whereIn('source', ['order', 'coupon'])->whereNumber('sourceId')->name('notifications.destroy');
});
