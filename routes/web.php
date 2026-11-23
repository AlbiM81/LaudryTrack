<?php

use App\Http\Controllers\OwnerAccountController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login');
Route::view('/login', 'auth.login')->name('login');

Route::get('/register', [OwnerAccountController::class, 'create'])->name('register');
Route::post('/register', [OwnerAccountController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('register.store');

Route::get('/forgot-password', [OwnerAccountController::class, 'requestPasswordReset'])
    ->name('password.request');
Route::post('/forgot-password', [OwnerAccountController::class, 'sendPasswordResetLink'])
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/reset-password/{token}', [OwnerAccountController::class, 'resetForm'])
    ->name('password.reset');
Route::post('/reset-password', [OwnerAccountController::class, 'resetPassword'])
    ->middleware('throttle:5,1')
    ->name('password.update');
