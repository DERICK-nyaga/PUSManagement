<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\HTTP\Controllers\Api\DashboardController;

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
