<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LossController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


Route::resource('/stations', StationController::class);
Route::resource('/employees', EmployeeController::class);
Route::resource('/losses', LossController::class);
Route::get('stations/{station}/losses', [LossController::class, 'stationLosses'])->name('stations.losses');
Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
