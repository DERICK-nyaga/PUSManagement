<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\EmployeeController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
// Route::get('/stations', function () {
//     return view('stations');
// })->name('stations');


Route::resource('/stations', StationController::class);
Route::resource('/employees', EmployeeController::class);
