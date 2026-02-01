<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\DashboardController;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\EmployeeController;
// use App\Http\Controllers\Api\PaymentController;
// use App\Http\Controllers\Api\DeductionTransactionController;
// use App\Http\Controllers\Api\StationController;
// use App\Http\Controllers\Api\InternetProviderController;
// use App\Http\Controllers\Api\UserController;

// // Public routes (if any)
// Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);

//     Route::get('/dashboard', [DashboardController::class, 'index']);

// // Protected routes
// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/profile', [AuthController::class, 'profile']);
//     Route::post('/change-password', [AuthController::class, 'changePassword']);
//     Route::post('/refresh-token', [AuthController::class, 'refresh']);
//     // Dashboard
//     // Route::get('/dashboard', [DashboardController::class, 'index']);

//     // Employees
//     Route::apiResource('employees', EmployeeController::class);
//     Route::get('/employees/{id}/deductions', [EmployeeController::class, 'deductions']);

//     // Stations
//     Route::apiResource('stations', StationController::class);
//     Route::get('/stations/{stationId}/payments', [StationController::class, 'stationPayments']);

//     // Payments - Internet
//     Route::prefix('payments/internet')->group(function () {
//         Route::get('/', [PaymentController::class, 'internetIndex']);
//         Route::post('/', [PaymentController::class, 'internetStore']);
//         Route::get('/{id}', [PaymentController::class, 'internetShow']);
//         Route::put('/{id}', [PaymentController::class, 'internetUpdate']);
//         Route::delete('/{id}', [PaymentController::class, 'internetDestroy']);
//         Route::get('/upcoming', [PaymentController::class, 'upcomingPayments']);
//         Route::get('/overdue', [PaymentController::class, 'overduePayments']);
//     });

//     // Payments - Airtime
//     Route::prefix('payments/airtime')->group(function () {
//         Route::get('/', [PaymentController::class, 'airtimeIndex']);
//         Route::post('/', [PaymentController::class, 'airtimeStore']);
//         Route::get('/{id}', [PaymentController::class, 'airtimeShow']);
//         Route::put('/{id}', [PaymentController::class, 'airtimeUpdate']);
//         Route::delete('/{id}', [PaymentController::class, 'airtimeDestroy']);
//     });

//     // Deduction Transactions
//     Route::apiResource('deductions', DeductionTransactionController::class);
//     Route::get('/employees/{employeeId}/transactions', [DeductionTransactionController::class, 'employeeTransactions']);
//     Route::get('/employees/{employeeId}/current-balance', [DeductionTransactionController::class, 'getCurrentBalance']);

//     // Internet Providers
//     Route::apiResource('internet-providers', InternetProviderController::class);

//     // Users
//     Route::apiResource('users', UserController::class)->middleware('admin');
//         // User management
//     Route::prefix('users')->group(function () {
//         // Profile routes (accessible to all authenticated users)
//         Route::get('/profile', [UserController::class, 'profile']);
//         Route::put('/profile', [UserController::class, 'updateProfile']);

//         // Admin-only routes
//         Route::middleware(['role:admin'])->group(function () {
//             Route::get('/', [UserController::class, 'index']);
//             Route::post('/', [UserController::class, 'store']);
//             Route::get('/stats', [UserController::class, 'stats']);
//             Route::get('/roles', [UserController::class, 'roles']);
//         });
//     });


//         Route::prefix('internet-providers')->group(function () {
//         Route::get('/', [InternetProviderController::class, 'index']);
//         Route::post('/', [InternetProviderController::class, 'store']);
//         Route::get('/stats', [InternetProviderController::class, 'stats']);
//         Route::get('/all', [InternetProviderController::class, 'getAll']);
//         Route::get('/search', [InternetProviderController::class, 'search']);
//     });

//     Route::apiResource('internet-providers', InternetProviderController::class)->except(['index']);

//     // Logout
//     Route::post('/logout', [AuthController::class, 'logout']);
// });

//     // Standard resource routes with admin middleware
//     Route::middleware(['role:admin'])->group(function () {
//         Route::apiResource('users', UserController::class)->except(['index', 'store']);
//     });
