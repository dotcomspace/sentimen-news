<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SentimenController;

// Halaman utama
Route::get('/', [SentimenController::class, 'index']);
