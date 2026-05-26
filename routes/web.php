<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});
Route::get('/category/pizza', function () {
    return view('category.show');
});
Route::get('/attendance', function () {
    return view('attendance.face-recognition');
});
