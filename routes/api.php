<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('role:PEMILIK')->get('/owner/check', function () {
        return response()->json(['message' => 'Akses Pemilik diterima']);
    });

    Route::middleware('role:KASIR,PEMILIK')->get('/staff/check', function () {
        return response()->json(['message' => 'Akses Kasir/Pemilik diterima']);
    });
});