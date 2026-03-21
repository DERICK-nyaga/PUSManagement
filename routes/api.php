<?php

use App\Http\Controllers\EmployeeProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InternetPaymentController;

Route::prefix('employees')->group(function () {
    Route::get('/', [EmployeeProfileController::class, 'index']);
    Route::post('/', [EmployeeProfileController::class, 'store']);
    Route::get('/{employee}', [EmployeeProfileController::class, 'show']);
    Route::put('/{employee}', [EmployeeProfileController::class, 'update']);
    Route::post('/{employee}/qualifications', [EmployeeProfileController::class, 'addQualification']);
    Route::post('/{employee}/terminate', [EmployeeProfileController::class, 'terminate']);
    Route::get('/{employee}/history', [EmployeeProfileController::class, 'changeHistory']);
});

Route::prefix('documents')->group(function () {
    Route::get('/{document}/download', [EmployeeProfileController::class, 'downloadDocument']);
    Route::get('/{document}/view', [EmployeeProfileController::class, 'viewDocument']);
});

Route::prefix('v1')->group(function () {
    Route::prefix('internet-payments')->group(function () {
        Route::get('/', [InternetPaymentController::class, 'index']);
        Route::post('/', [InternetPaymentController::class, 'store']);
        Route::get('/due-summary', [InternetPaymentController::class, 'dueSummary']);
        Route::get('/overdue', [InternetPaymentController::class, 'overdue']);
        Route::get('/due-soon', [InternetPaymentController::class, 'dueSoon']);
        Route::get('/{internetPayment}', [InternetPaymentController::class, 'show']);
        Route::put('/{internetPayment}', [InternetPaymentController::class, 'update']);
        Route::delete('/{internetPayment}', [InternetPaymentController::class, 'destroy']);
        Route::post('/{internetPayment}/mark-paid', [InternetPaymentController::class, 'markPaid']);
        Route::post('/{internetPayment}/send-reminder', [InternetPaymentController::class, 'sendReminder']);
        Route::post('/bulk/mark-paid', [InternetPaymentController::class, 'bulkMarkPaid']);
        Route::post('/bulk/send-reminders', [InternetPaymentController::class, 'bulkSendReminders']);
        Route::get('/station/{stationId}/due', [InternetPaymentController::class, 'stationInternetDue']);
    });
});
