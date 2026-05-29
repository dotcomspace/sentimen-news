<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimenController;

Route::post('/v1/analyze',  [SentimenController::class, 'analyze']);
Route::get('/v1/news',      [SentimenController::class, 'news']);
Route::get('/v1/history',   [SentimenController::class, 'historyApi']);
Route::get('/v1/stats',     [SentimenController::class, 'stats']);
