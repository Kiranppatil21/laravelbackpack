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
    // Super Admin: agency leads cross-tenant listing and details
    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('agency-leads', 'DashboardController@agencyLeads')->name('admin.agency.leads');
        Route::get('agency-leads/{tenantUuid}/{agencyId}/followups', 'DashboardController@agencyFollowups')->name('admin.agency.leads.followups');
        Route::post('agency-leads/{tenantUuid}/{agencyId}/followups', 'DashboardController@storeFollowup')->name('admin.agency.leads.followups.store');
        Route::get('agency-leads/{tenantUuid}/{agencyId}', 'DashboardController@agencyDetails')->name('admin.agency.leads.show');
    });
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
    // Quick-create endpoint for Super Admin modal
    Route::post('agency/quick-create', 'DashboardController@quickCreateAgency')->name('admin.agency.quick_create');
    // Route::crud('client', 'ClientCrudController'); // Removed duplicate - defined below with Super Admin protection
    Route::crud('employee', 'EmployeeCrudController');
    // Generate ID card for employee
    Route::get('employee/{id}/generate-id-card', 'EmployeeCrudController@generateIdCard')->name('admin.employee.generate-id-card');
    Route::crud('attendance', 'AttendanceCrudController');
    Route::crud('test-attendance-crud', 'TestAttendanceCrudController');
    Route::crud('payroll', 'PayrollCrudController');
    // Admin endpoint to generate a payslip from a payroll record
    Route::get('payroll/{id}/generate-payslip', 'PayrollCrudController@generatePayslip')->name('admin.payroll.generate');
    Route::crud('invoice', 'InvoiceCrudController');
    
    // Custom Client Routes (accessible to more roles)
    Route::prefix('client')->name('client.')->group(function () {
        Route::get('create-custom', 'ClientController@create')->name('create-custom');
        Route::post('store-custom', 'ClientController@store')->name('store-custom');
        Route::get('{client}/edit-custom', 'ClientController@edit')->name('edit-custom');
        Route::put('{client}/update-custom', 'ClientController@update')->name('update-custom');
        Route::get('{client}/contacts', 'ClientController@contacts')->name('contacts');
        Route::get('{client}/taxes', 'ClientController@taxes')->name('taxes');
    });
    
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
        
        // Client Management Routes for Admin (Super Admin only)
        Route::crud('client', 'ClientCrudController');
    });
    
    // Company Job Openings Management (Backpack CRUD)
    Route::crud('company-job-openings', 'CompanyJobOpeningCrudController');
    // Payslip management via Backpack CRUD
    // Test route for debugging AJAX issues
    Route::get('test-ajax', function() {
        return view('admin.test-ajax');
    })->name('admin.test.ajax');

    Route::crud('payslip', 'PayslipCrudController');
    
    // Bulk Attendance Management
    Route::group(['prefix' => 'bulk-attendance', 'as' => 'admin.bulk-attendance.'], function () {
        Route::get('/', 'BulkAttendanceController@index')->name('index');
        Route::post('search', 'BulkAttendanceController@search')->name('search');
        Route::post('store', 'BulkAttendanceController@store')->name('store');
        Route::post('{id}/submit', 'BulkAttendanceController@submit')->name('submit');
        Route::post('{id}/approve', 'BulkAttendanceController@approve')->name('approve');
        Route::post('{id}/lock', 'BulkAttendanceController@lock')->name('lock');
        Route::get('{id}/audits', 'BulkAttendanceController@audits')->name('audits');
        Route::get('{id}/summary', 'BulkAttendanceController@summary')->name('summary');
        Route::get('{id}/export.csv', 'BulkAttendanceController@exportCsv')->name('export.csv');
        Route::get('view', 'BulkAttendanceController@view')->name('view');
        Route::get('{id}/show', 'BulkAttendanceController@show')->name('show');
        Route::delete('{id}', 'BulkAttendanceController@destroy')->name('destroy');
        Route::delete('delete', 'BulkAttendanceController@deleteBulk')->name('delete-bulk');
    });
    Route::crud('client-invoice', 'ClientInvoiceCrudController');
    
    // Additional routes for Client Invoice functionality
    Route::group(['prefix' => 'client-invoice', 'as' => 'admin.client-invoice.'], function () {
        Route::post('get-attendance', 'ClientInvoiceCrudController@getEmployeeAttendance')->name('get-attendance');
        Route::get('{id}/pdf', 'ClientInvoiceCrudController@generatePDF')->name('pdf');
    });
    
    // Employee ID Card generation routes
    Route::group(['prefix' => 'employee', 'as' => 'admin.employee.'], function () {
        Route::get('{id}/generate-id-card', 'EmployeeCrudController@generateIdCard')->name('generate-id-card');
        Route::get('{id}/preview-id-card', 'EmployeeCrudController@previewIdCard')->name('preview-id-card');
        Route::get('{id}/preview-data', 'EmployeeCrudController@getPreviewData')->name('preview-data');
        Route::post('bulk-generate-id-cards', 'EmployeeCrudController@bulkGenerateIdCards')->name('bulk-generate-id-cards');
        Route::post('bulk-assign-client', 'EmployeeCrudController@bulkAssignClient')->name('bulk-assign-client');
    });
    
    // API routes for bulk assignment
    Route::group(['prefix' => 'api', 'as' => 'admin.api.'], function () {
        Route::get('clients', 'EmployeeCrudController@getClientsApi')->name('clients');
        Route::get('employees', 'EmployeeCrudController@getEmployeesApi')->name('employees');
    });
    
    // Inventory Management Routes
    Route::crud('asset', 'AssetCrudController');
    Route::crud('supplier', 'SupplierCrudController');
    Route::crud('purchase-order', 'PurchaseOrderCrudController');
    Route::crud('inventory-transaction', 'InventoryTransactionCrudController');
    
    // HR Management Routes
    Route::crud('leave', 'LeaveCrudController');
    Route::crud('shift', 'ShiftCrudController');
    
    // Training & Development Routes
    Route::crud('training', 'TrainingCrudController');
    
    // Operations Management Routes
    Route::crud('incident', 'IncidentCrudController');
    Route::crud('contract', 'ContractCrudController');
    
    // Report Routes
    Route::group(['prefix' => 'reports', 'as' => 'admin.reports.'], function () {
        // Leave Reports
        Route::get('leave', 'LeaveReportController@index')->name('leave.index');
        Route::get('leave/generate', 'LeaveReportController@generate')->name('leave.generate');
        Route::get('leave/export-pdf', 'LeaveReportController@exportPdf')->name('leave.pdf');
        Route::get('leave/export-excel', 'LeaveReportController@exportExcel')->name('leave.excel');
        Route::get('leave/export-csv', 'LeaveReportController@exportCsv')->name('leave.csv');
        
        // Shift Reports
        Route::get('shift', 'ShiftReportController@index')->name('shift.index');
        Route::get('shift/generate', 'ShiftReportController@generate')->name('shift.generate');
        Route::get('shift/export-pdf', 'ShiftReportController@exportPdf')->name('shift.pdf');
        Route::get('shift/export-excel', 'ShiftReportController@exportExcel')->name('shift.excel');
        Route::get('shift/export-csv', 'ShiftReportController@exportCsv')->name('shift.csv');
        
        // Training Reports
        Route::get('training', 'TrainingReportController@index')->name('training.index');
        Route::get('training/generate', 'TrainingReportController@generate')->name('training.generate');
        Route::get('training/export-pdf', 'TrainingReportController@exportPdf')->name('training.pdf');
        Route::get('training/export-excel', 'TrainingReportController@exportExcel')->name('training.excel');
        Route::get('training/export-csv', 'TrainingReportController@exportCsv')->name('training.csv');
        
        // Incident Reports
        Route::get('incident', 'IncidentReportController@index')->name('incident.index');
        Route::get('incident/generate', 'IncidentReportController@generate')->name('incident.generate');
        Route::get('incident/export-pdf', 'IncidentReportController@exportPdf')->name('incident.pdf');
        Route::get('incident/export-excel', 'IncidentReportController@exportExcel')->name('incident.excel');
        Route::get('incident/export-csv', 'IncidentReportController@exportCsv')->name('incident.csv');
        
        // Contract Reports
        Route::get('contract', 'ContractReportController@index')->name('contract.index');
        Route::get('contract/generate', 'ContractReportController@generate')->name('contract.generate');
        Route::get('contract/export-pdf', 'ContractReportController@exportPdf')->name('contract.pdf');
        Route::get('contract/export-excel', 'ContractReportController@exportExcel')->name('contract.excel');
        Route::get('contract/export-csv', 'ContractReportController@exportCsv')->name('contract.csv');
    });
    
    // Employee Active/Deactive Routes
    Route::group(['prefix' => 'employee', 'as' => 'admin.employee.'], function () {
        Route::post('{id}/toggle-status', 'EmployeeCrudController@toggleStatus')->name('toggle-status');
    });
    
    // Deactivated Employees Route (separate CRUD view)
    Route::crud('deactivated-employee', 'DeactivatedEmployeeCrudController');
    
    // Menu Permission Management
    Route::crud('menu-permission', 'MenuPermissionCrudController');
    Route::get('menu-permission/{id}/manage-access', 'MenuPermissionCrudController@manageAccess')->name('admin.menu-permission.manage-access');
    Route::post('menu-permission/{id}/save-access', 'MenuPermissionCrudController@saveAccess')->name('admin.menu-permission.save-access');
    Route::get('menu-permission/seed-menus', 'MenuPermissionCrudController@seedMenus')->name('admin.menu-permission.seed');
}); // this should be the absolute last line of this file

// Test route for ID card generation (outside admin middleware for easy testing)
Route::get('test-id-card/{id}', function($id) {
    $employee = \App\Models\Employee::with('agency')->findOrFail($id);
    return view('admin.employee.id_card', compact('employee'));
})->name('test-id-card');

/**
 * DO NOT ADD ANYTHING HERE.
 */
