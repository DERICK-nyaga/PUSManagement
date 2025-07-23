<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LossController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorCategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\DeductionTransactionController;

Route::get('/test', [TestController::class, 'index']);

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve')->middleware('auth');

Route::post('/payments/{payment}/mark-as-paid', [PaymentController::class, 'markAsPaid'])->name('payments.markAsPaid')->middleware('auth');
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])
         ->name('register');

    Route::post('register', [RegisterController::class, 'register'])->middleware('throttle:5,1');

    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])
         ->name('password.request');

    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
         ->name('password.email');

    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])
         ->name('password.reset');

    Route::post('password/reset', [ResetPasswordController::class, 'reset'])
         ->name('password.update');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('payments/{payment}/approve', [PaymentController::class, 'approve']);
    Route::post('payments/{payment}/reject', [PaymentController::class, 'reject']);
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('reports', [ReportController::class, 'index']);
});

Route::middleware(['auth', 'roles:admin,manager'])->group(...);
Route::middleware(['auth'])->group(function () {
    Route::resource('reports', ReportController::class);
    Route::get('reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
});

Route::middleware(['auth'])->group(
    function (){
        // Route::resource('/deductions', DeductionTransactionController::class);
        Route::get('/deductions', [DeductionTransactionController::class, 'index'])->name('deductions.index');
        Route::get('/deductions/show', [DeductionTransactionController::class, 'show'])->name('deductions.show');
        Route::get('/deductions/create', [DeductionTransactionController::class, 'create'])->name('deductions.create');
        Route::post('/deductions/store', [DeductionTransactionController::class, 'store'])->name('deductions.store');
        Route::post('/deductions/edit', [DeductionTransactionController::class, 'edit'])->name('deductions.edit');
        Route::post('/deductions/delete', [DeductionTransactionController::class, 'destroy'])->name('deductions.destroy');
        Route::get('employees/deductions', [DeductionTransactionController::class, 'employeeTransactions'])
            ->name('employees.deductions');

        Route::get('/reports', [ReportController::class, 'create'])->name('reports');

        Route::get('/approve', [ReportController::class, 'approve'])->name('approve');
        Route::get('/handlereports', [ReportController::class, 'index'])->name('CheckReports');
        Route::get('/masters', [ReportController::class, 'masters'])->name('ViewAll');
        Route::get('reports/{report}/download', [ReportController::class, 'download'])
            ->name('reports.download');
        Route::get('/Checkpayments', [PaymentController::class, 'index'])->name('payments');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::resource('/stations', StationController::class);
        Route::resource('/employees', EmployeeController::class);
        Route::resource('/losses', LossController::class);
        Route::get('stations/{station}/losses', [LossController::class, 'stationLosses'])->name('stations.losses');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::resource('vendors', VendorController::class);
        Route::get('vendors/{vendor}/download-contract', [VendorController::class, 'downloadContract'])
            ->name('vendors.contract.download');
        Route::resource('vendor-categories', VendorCategoryController::class);;
        Route::resource('users', UserController::class);

        Route::get('/logout', function () {
            Auth::logout();
            return redirect('/login')->with('status', 'You have been logged out.');
        })->name('logout');
    }
);
