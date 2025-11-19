<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    // Enhanced Dashboard
    Route::get('dashboard', 'DashboardController@index')->name('backpack.dashboard');
    
    // CSRF token refresh for AJAX requests
    Route::get('refresh-csrf', 'DashboardController@refreshCsrf')->name('admin.refresh.csrf');
    
    // Test route to isolate attendance issues
    Route::get('test-attendance', function() {
        return response()->json([
            'message' => 'Attendance test route works',
            'user' => backpack_user()->name ?? 'No user',
            'attendance_count' => \App\Models\Attendance::count()
        ]);
    })->name('admin.test.attendance');
    
    // Simple attendance page without CRUD
    Route::get('simple-attendance', 'SimpleAttendanceController@index')->name('admin.simple.attendance');
    
    // JavaScript error test page
    Route::get('js-test', function() {
        return view('admin.js_test');
    })->name('admin.js.test');
    
    // Test create page
    Route::get('test-create', 'TestCreateController@index')->name('admin.test.create');
    
    // CSRF test page
    Route::get('csrf-test', 'CsrfTestController@index')->name('admin.csrf.test');
    Route::post('csrf-test', 'CsrfTestController@testPost')->name('admin.csrf.test.post');
    
    // Notification Routes
    Route::get('notifications', 'NotificationController@index')->name('admin.notifications.index');
    Route::post('notifications/{id}/read', 'NotificationController@markAsRead')->name('admin.notifications.read');
    Route::post('notifications/mark-all-read', 'NotificationController@markAllAsRead')->name('admin.notifications.mark_all_read');
    Route::get('notifications/count', 'NotificationController@getCount')->name('admin.notifications.count');
    Route::get('notifications/recent', 'NotificationController@getRecent')->name('admin.notifications.recent');
    
    Route::crud('user', 'UserCrudController');
    // Role & Permission CRUD (protected by Super Admin role)
    // Using different middleware syntax to avoid route/middleware name conflicts
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::crud('roles', 'RoleCrudController'); // Changed from 'role' to 'roles' to avoid conflict
        Route::crud('permissions', 'PermissionCrudController'); // Changed from 'permission' to 'permissions'
    });

    // AJAX helpers for inline permission creation/search used by select2_from_ajax
    // These are protected by the roles configured in config/backpack-permissions.php
    $ajaxRoles = implode('|', config('backpack-permissions.sidebar_allowed_roles', ['Super Admin']));
    Route::middleware(['role:'.$ajaxRoles])->group(function () {
        Route::post('permissions/ajax-create', 'PermissionCrudController@ajaxCreate')->name('permission.ajax.create');
        Route::get('permissions/ajax-search', 'PermissionCrudController@ajaxSearch')->name('permission.ajax.search');
    });
    Route::crud('agency', 'AgencyCrudController');
    Route::crud('client', 'ClientCrudController');
    Route::crud('employee', 'EmployeeCrudController');
    Route::crud('attendance', 'AttendanceCrudController');
    Route::crud('test-attendance-crud', 'TestAttendanceCrudController');
    Route::crud('payroll', 'PayrollCrudController');
    Route::crud('invoice', 'InvoiceCrudController');
    // Tenancy management (Super Admin only)
    Route::group(['middleware' => ['role:Super Admin']], function () {
        Route::crud('tenant', 'TenantCrudController');
        Route::crud('domain', 'DomainCrudController');
        // Admin endpoint to start a Stripe checkout session for a tenant
        Route::post('tenant/{id}/billing/checkout', 'BillingController@checkout')->name('tenant.billing.checkout');
        // Razorpay payments admin
        Route::crud('razorpay-payment', 'RazorpayPaymentCrudController');
        // allow both GET (from UI link) and POST (explicit action) for retry
        Route::get('razorpay-payments/{id}/retry', 'RazorpayPaymentCrudController@retry')->name('razorpay.payments.retry.get');
        Route::post('razorpay-payments/{id}/retry', 'RazorpayPaymentCrudController@retry')->name('razorpay.payments.retry');
    });
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
