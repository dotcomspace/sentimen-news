<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimenController;

// Halaman utama
Route::get('/', [SentimenController::class, 'index']);

// API routes dipanggil dari JavaScript di Blade
Route::prefix('api/v1')->group(function () {
    Route::post('/analyze',  [SentimenController::class, 'analyze']);
    Route::get('/news',      [SentimenController::class, 'news']);
    Route::get('/history',   [SentimenController::class, 'historyApi']);
    Route::get('/stats',     [SentimenController::class, 'stats']);
});
