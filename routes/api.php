<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;

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
    });
});
