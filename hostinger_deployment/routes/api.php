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

// Public mobile app endpoints
Route::get('/mobile/config', [App\Http\Controllers\Api\MobileAppController::class, 'getAppConfig']);
Route::post('/mobile/generate-qr', [App\Http\Controllers\Api\MobileAppController::class, 'generateQRCode']);
Route::post('/mobile/scan-qr', [App\Http\Controllers\Api\MobileAppController::class, 'scanQRCode']);
Route::get('/mobile/visitor-status', [App\Http\Controllers\Api\MobileAppController::class, 'getVisitorStatus']);
Route::get('/mobile/invitations', [App\Http\Controllers\Api\MobileAppController::class, 'getInvitations']);

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

    // Visitor management (Phase 5 enhanced)
    Route::prefix('visitors')->group(function () {
        // Basic CRUD operations
        Route::get('/', [App\Http\Controllers\Api\VisitorController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\VisitorController::class, 'store']);
        Route::get('/{visitor}', [App\Http\Controllers\Api\VisitorController::class, 'show']);
        Route::put('/{visitor}', [App\Http\Controllers\Api\VisitorController::class, 'update']);
        Route::delete('/{visitor}', [App\Http\Controllers\Api\VisitorController::class, 'destroy']);
        
        // Checkout uses visit log id — authenticated users can call this
        Route::post('/{visit}/checkout', [App\Http\Controllers\Api\VisitorController::class, 'checkOut']);
        
        // Enhanced visitor endpoints
        Route::get('/dashboard', [App\Http\Controllers\Api\VisitorController::class, 'dashboard']);
        Route::get('/logs', [App\Http\Controllers\Api\VisitorController::class, 'index']);
        
        // Visitor invitations
        Route::prefix('invitations')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\VisitorInvitationController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\VisitorInvitationController::class, 'store']);
            Route::get('/stats', [App\Http\Controllers\Api\VisitorInvitationController::class, 'stats']);
            Route::post('/validate', [App\Http\Controllers\Api\VisitorInvitationController::class, 'validate']);
            Route::get('/{invitation}', [App\Http\Controllers\Api\VisitorInvitationController::class, 'show']);
            Route::put('/{invitation}', [App\Http\Controllers\Api\VisitorInvitationController::class, 'update']);
            Route::delete('/{invitation}', [App\Http\Controllers\Api\VisitorInvitationController::class, 'cancel']);
            Route::post('/{invitation}/extend', [App\Http\Controllers\Api\VisitorInvitationController::class, 'extend']);
        });
        
        // Security management
        Route::prefix('security')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\SecurityController::class, 'dashboard']);
            Route::get('/reports', [App\Http\Controllers\Api\SecurityController::class, 'reports']);
            
            // Alerts
            Route::get('/alerts', [App\Http\Controllers\Api\SecurityController::class, 'alerts']);
            Route::get('/alerts/{alert}', [App\Http\Controllers\Api\SecurityController::class, 'showAlert']);
            Route::post('/alerts/{alert}/assign', [App\Http\Controllers\Api\SecurityController::class, 'assignAlert']);
            Route::post('/alerts/{alert}/resolve', [App\Http\Controllers\Api\SecurityController::class, 'resolveAlert']);
            Route::post('/alerts/{alert}/escalate', [App\Http\Controllers\Api\SecurityController::class, 'escalateAlert']);
            
            // Watchlist
            Route::get('/watchlist', [App\Http\Controllers\Api\SecurityController::class, 'watchlist']);
            Route::post('/watchlist', [App\Http\Controllers\Api\SecurityController::class, 'addToWatchlist']);
            Route::delete('/watchlist/{watchlistEntry}', [App\Http\Controllers\Api\SecurityController::class, 'removeFromWatchlist']);
            Route::put('/watchlist/{watchlistEntry}', [App\Http\Controllers\Api\SecurityController::class, 'updateWatchlistEntry']);
        });
        
        // Analytics and reporting
        Route::prefix('analytics')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\Api\VisitorAnalyticsController::class, 'dashboard']);
            Route::get('/compliance-report', [App\Http\Controllers\Api\VisitorAnalyticsController::class, 'complianceReport']);
            Route::post('/export-visitor-data', [App\Http\Controllers\Api\VisitorAnalyticsController::class, 'exportVisitorData']);
        });
        
        // IoT device management
        Route::prefix('devices')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\IoTDeviceController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\IoTDeviceController::class, 'store']);
            Route::get('/stats', [App\Http\Controllers\Api\IoTDeviceController::class, 'deviceStats']);
            Route::post('/batch-update', [App\Http\Controllers\Api\IoTDeviceController::class, 'batchUpdate']);
            Route::get('/{device}', [App\Http\Controllers\Api\IoTDeviceController::class, 'show']);
            Route::put('/{device}', [App\Http\Controllers\Api\IoTDeviceController::class, 'update']);
            Route::delete('/{device}', [App\Http\Controllers\Api\IoTDeviceController::class, 'destroy']);
        });
    });

    // Public IoT device endpoints (protected by device authentication)
    Route::prefix('iot')->middleware(\App\Http\Middleware\VerifyVisitorApiKey::class)->group(function () {
        Route::post('/heartbeat', [App\Http\Controllers\Api\IoTDeviceController::class, 'heartbeat']);
        Route::post('/checkin', [App\Http\Controllers\Api\IoTDeviceController::class, 'deviceCheckIn']);
        Route::post('/checkout', [App\Http\Controllers\Api\IoTDeviceController::class, 'deviceCheckOut']);
    });

    // Mobile app endpoints (authenticated)
    Route::prefix('mobile')->group(function () {
        Route::post('/register-device', [App\Http\Controllers\Api\MobileAppController::class, 'registerDevice']);
        Route::post('/upload-photo', [App\Http\Controllers\Api\MobileAppController::class, 'uploadPhoto']);
        Route::post('/checkout', [App\Http\Controllers\Api\MobileAppController::class, 'checkOut']);
        Route::get('/visit-history', [App\Http\Controllers\Api\MobileAppController::class, 'getVisitHistory']);
        Route::post('/accept-invitation', [App\Http\Controllers\Api\MobileAppController::class, 'acceptInvitation']);
        Route::get('/contact-tracing', [App\Http\Controllers\Api\MobileAppController::class, 'getContactTracing']);
        Route::post('/test-notification', [App\Http\Controllers\Api\MobileAppController::class, 'sendTestNotification']);
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
