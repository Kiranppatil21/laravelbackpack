<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PayslipController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public visitor check-in for kiosks / IoT devices (optionally protected by header)
Route::post('/visitors/checkin', [\App\Http\Controllers\Api\VisitorController::class, 'checkIn'])->middleware(\App\Http\Middleware\VerifyVisitorApiKey::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard endpoint — role enforcement performed inside controller to avoid middleware alias issues in some environments
    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'show']);

    // Agencies & Clients API for React frontend / SPA
    Route::apiResource('agencies', \App\Http\Controllers\Api\AgencyController::class);
    Route::apiResource('clients', \App\Http\Controllers\Api\ClientController::class);
        Route::apiResource('employees', EmployeeController::class);
    
    // Attendance API (Phase 4 scaffolding)
    Route::prefix('attendance')->group(function () {
        Route::post('/checkin', [App\Http\Controllers\Api\AttendanceController::class, 'checkIn']);
        Route::post('/checkout', [App\Http\Controllers\Api\AttendanceController::class, 'checkOut']);
        Route::get('/reports', [App\Http\Controllers\Api\AttendanceController::class, 'report']);
                Route::get('payslips/{payslip}/download', [PayslipController::class, 'download']);
    });

    // Visitor management (Phase 5 scaffolding)
    Route::prefix('visitors')->group(function () {
        // Checkout uses visit log id — authenticated users can call this
        Route::post('/{visit}/checkout', [App\Http\Controllers\Api\VisitorController::class, 'checkOut']);
        // Listing of visit logs (requires auth)
        Route::get('/logs', [App\Http\Controllers\Api\VisitorController::class, 'index']);
    });

    // Finance & Compliance (Phase 6 scaffolding)
    Route::prefix('finance')->group(function () {
        Route::get('/invoices', [\App\Http\Controllers\Api\FinanceController::class, 'indexInvoices']);
        Route::post('/invoices', [\App\Http\Controllers\Api\FinanceController::class, 'storeInvoice']);
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\FinanceController::class, 'showInvoice']);
        Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\Api\FinanceController::class, 'recordPayment']);
        // Reports
        Route::post('/reports/statutory', [\App\Http\Controllers\Api\FinanceController::class, 'generateStatutoryReport']);
            // Ad-hoc CSV streaming (does not persist a StatutoryReport)
            Route::post('/reports/statutory/download', [\App\Http\Controllers\Api\FinanceController::class, 'downloadStatutoryReportAdHoc']);
    Route::get('/reports/statutory/{report}/download', [\App\Http\Controllers\Api\FinanceController::class, 'downloadStatutoryReportCsv']);
        Route::get('/reports/profitability', [\App\Http\Controllers\Api\FinanceController::class, 'profitability']);
    });
});
