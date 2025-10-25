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
    Route::crud('user', 'UserCrudController');
    // Role & Permission CRUD (protected by Super Admin role)
    Route::group(['middleware' => ['role:Super Admin']], function () {
        Route::crud('role', 'RoleCrudController');
        Route::crud('permission', 'PermissionCrudController');
    });

    // AJAX helpers for inline permission creation/search used by select2_from_ajax
    // These are protected by the roles configured in config/backpack-permissions.php
    $ajaxRoles = implode('|', config('backpack-permissions.sidebar_allowed_roles', ['Super Admin']));
    Route::group(['middleware' => ['role:'.$ajaxRoles]], function () {
        Route::post('permission/ajax-create', 'PermissionCrudController@ajaxCreate')->name('permission.ajax.create');
        Route::get('permission/ajax-search', 'PermissionCrudController@ajaxSearch')->name('permission.ajax.search');
    });
    Route::crud('agency', 'AgencyCrudController');
    Route::crud('client', 'ClientCrudController');
    Route::crud('employee', 'EmployeeCrudController');
    Route::crud('attendance', 'AttendanceCrudController');
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
