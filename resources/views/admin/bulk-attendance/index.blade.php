@extends(backpack_view('blank'))

@push('before_styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@php
  $defaultBreadcrumbs = [
    'Admin' => url(config('backpack.base.route_prefix'), 'dashboard'),
    'Bulk Attendance' => false,
  ];
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid">
  <h2>
    <span class="text-capitalize">Allocate Employee To Client - Bulk Attendance</span>
    <small>Raj Security Services</small>
  </h2>
</section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        
        {{-- Information Alert --}}
        <div class="alert alert-info">
            <h5><i class="la la-info-circle"></i> Client Assignment Feature</h5>
            <p class="mb-2">📍 <strong>Employee-Client Assignment:</strong> Employees can now be assigned to specific client locations during registration.</p>
            <p class="mb-2">🔍 <strong>Smart Filtering:</strong> When you select a site, only employees assigned to that client will be shown for attendance marking.</p>
            <p class="mb-0">⚙️ <strong>To assign employees:</strong> Go to <strong>Admin → Employees → Edit Employee</strong> and select a client in the "Client Assignment" section.</p>
        </div>
        
        {{-- Header Section - Allocation Filters --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attendance Management</h3>
            </div>
            <div class="card-body">
                <form id="attendance-search-form" class="row g-3">
                    {{-- Site Selection --}}
                    <div class="col-md-3">
                        <label for="site_id" class="form-label"><strong>Site Name *</strong></label>
                        <select id="site_id" name="site_id" class="form-control" required>
                            <option value="">Select Site</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Only employees assigned to this client will be shown</small>
                    </div>

                    {{-- Employee Designation Selection --}}
                    <div class="col-md-3">
                        <label for="user_type" class="form-label"><strong>Employee Designation *</strong></label>
                        <select id="user_type" name="user_type" class="form-control" required>
                            <option value="">Select Designation</option>
                            @foreach($userTypes as $key => $type)
                                <option value="{{ $key }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Month Selection --}}
                    <div class="col-md-3">
                        <label for="month" class="form-label"><strong>Month & Year Selection *</strong></label>
                        <div class="row">
                            <div class="col-6">
                                <select id="month_select" name="month_select" class="form-control" required>
                                    <option value="01" {{ date('m') == '01' ? 'selected' : '' }}>January</option>
                                    <option value="02" {{ date('m') == '02' ? 'selected' : '' }}>February</option>
                                    <option value="03" {{ date('m') == '03' ? 'selected' : '' }}>March</option>
                                    <option value="04" {{ date('m') == '04' ? 'selected' : '' }}>April</option>
                                    <option value="05" {{ date('m') == '05' ? 'selected' : '' }}>May</option>
                                    <option value="06" {{ date('m') == '06' ? 'selected' : '' }}>June</option>
                                    <option value="07" {{ date('m') == '07' ? 'selected' : '' }}>July</option>
                                    <option value="08" {{ date('m') == '08' ? 'selected' : '' }}>August</option>
                                    <option value="09" {{ date('m') == '09' ? 'selected' : '' }}>September</option>
                                    <option value="10" {{ date('m') == '10' ? 'selected' : '' }}>October</option>
                                    <option value="11" {{ date('m') == '11' ? 'selected' : '' }}>November</option>
                                    <option value="12" {{ date('m') == '12' ? 'selected' : '' }}>December</option>
                                </select>
                                <small class="text-muted">Month</small>
                            </div>
                            <div class="col-6">
                                <select id="year_select" name="year_select" class="form-control" required>
                                    @for($year = date('Y') - 2; $year <= date('Y') + 1; $year++)
                                        <option value="{{ $year }}" {{ date('Y') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endfor
                                </select>
                                <small class="text-muted">Year</small>
                            </div>
                        </div>
                        <input type="hidden" id="month" name="month" value="{{ date('Y-m') }}">
                    </div>

                    {{-- Shift Selection Checkboxes (moved inside form) --}}
                    <div class="col-md-12">
                        <label class="form-label"><strong>Shift Selection</strong></label>
                        <div class="d-flex gap-3 mb-3">
                            @foreach($shifts as $key => $shift)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="shifts[]" 
                                           value="{{ $key }}" id="shift_{{ $key }}" checked>
                                    <label class="form-check-label" for="shift_{{ $key }}">
                                        {{ $shift }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    {{-- Search Button --}}
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="la la-search"></i> Search Employees
                        </button>
                        <button type="button" class="btn btn-success" id="allocate-btn" disabled>
                            <i class="la la-users"></i> Allocate
                        </button>
                        <button type="button" class="btn btn-secondary me-2" id="clear-btn">
                            <i class="la la-refresh"></i> Clear Form
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Loading Spinner --}}
        <div id="loading-spinner" class="text-center mt-3" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading employees and calendar...</p>
        </div>

        {{-- Attendance Table --}}
        <div id="attendance-section" class="card mt-3" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Attendance Grid</h3>
                <div>
                    {{-- Edit Mode Controls (Initially Hidden) --}}
                    <div id="edit-mode-controls" style="display: none;">
                        <button type="button" class="btn btn-info btn-sm me-1" id="revert-changes-btn" title="Revert to Original">
                            <i class="la la-undo"></i> Revert
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm me-1" id="clear-month-btn" title="Clear All Month">
                            <i class="la la-eraser"></i> Clear All
                        </button>
                        <button type="button" class="btn btn-warning btn-sm me-2" id="delete-attendance-btn" title="Delete Attendance Record">
                            <i class="la la-trash"></i> Delete Record
                        </button>
                    </div>
                    
                    <button type="button" class="btn btn-warning me-2" id="remove-shift-btn">
                        <i class="la la-trash"></i> Remove Shift
                    </button>
                    <button type="button" class="btn btn-success" id="submit-attendance-btn">
                        <i class="la la-save"></i> Submit Full Month Attendance
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Attendance Mode Information --}}
                <div id="attendance-info">
                    {{-- Info message will be populated by JavaScript --}}
                </div>
                
                {{-- Bulk Actions --}}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h5 class="mb-2">
                            <i class="la la-tasks"></i> Bulk Actions 
                            <small class="text-muted">(Apply to ALL shifts + OT)</small>
                        </h5>
                        <div class="alert alert-warning mb-2">
                            <strong>🚨 DIRECT TEST PANEL</strong><br>
                            Click these test buttons to verify functionality:<br>
                            <button type="button" class="btn btn-danger btn-sm mt-1" onclick="directTestAllDays()">🧪 Test All Days</button>
                            <button type="button" class="btn btn-danger btn-sm mt-1" onclick="directTestExceptSunday()">🧪 Test Except Sunday</button>
                            <button type="button" class="btn btn-danger btn-sm mt-1" onclick="directTestClear()">🧪 Test Clear All</button>
                            <br><small class="text-danger">These bypass event handlers for direct testing</small>
                        </div>
                        <div class="alert alert-info mb-2">
                            <strong>📋 Testing Instructions:</strong>
                            <ol class="mb-1">
                                <li>First <strong>search for employees</strong> using the form above</li>
                                <li>Once the attendance table appears, <strong>click any bulk action button</strong></li>
                                <li>Open browser console (F12) to see detailed debugging info</li>
                            </ol>
                            <strong>🧪 Console Debugging Commands:</strong>
                            <ul class="mb-0">
                                <li><code>window.testBulkActions()</code> - Test if bulk actions work</li>
                                <li><code>window.testWeekendLogic()</code> - Debug weekend date parsing</li>
                            </ul>
                            <small class="text-success">✅ <strong>Fixed:</strong> Buttons no longer disappear after clicking</small>
                        </div>
                        <div class="btn-group flex-wrap" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="all-days" onclick="window.directTestAllDays && window.directTestAllDays()">📅 All Days (S1 Only)</button>
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="except-sunday" onclick="window.directTestExceptSunday && window.directTestExceptSunday()">📅 Except Sunday (S1 Only)</button>
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="except-saturday" onclick="directTestExceptSaturday()">📅 Except Saturday (S1 Only)</button>
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="except-weekend" onclick="directTestExceptWeekend()">📅 Monday-Friday (S1 Only)</button>
                            <button type="button" class="btn btn-outline-info btn-sm bulk-check" 
                                    data-action="shift-1" onclick="directTestShift1()">S1 Only</button>
                            <button type="button" class="btn btn-outline-info btn-sm bulk-check" 
                                    data-action="shift-2" onclick="directTestShift2()">S2 Only</button>
                            <button type="button" class="btn btn-outline-info btn-sm bulk-check" 
                                    data-action="shift-3" onclick="directTestShift3()">S3 Only</button>
                            <button type="button" class="btn btn-outline-warning btn-sm bulk-check" 
                                    data-action="clear-all" onclick="window.directTestClear && window.directTestClear()">🗑️ Clear All</button>
                        </div>
                    </div>
                </div>

                {{-- Enhanced Scrollable Table Container --}}
                <div class="table-responsive" style="max-height: 75vh; overflow: auto; border: 3px solid #0d6efd; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1);">
                    <table class="table table-bordered table-hover mb-0" id="attendance-table" style="font-size: 0.85rem; min-width: 100%; width: max-content;">
                        <thead class="sticky-top" id="table-head" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
                            {{-- Table header will be completely regenerated by JavaScript --}}
                        </thead>
                        <tbody id="attendance-tbody">
                            {{-- Employee rows will be generated dynamically --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Success/Error Messages --}}
<div class="row mt-3">
    <div class="col-md-12">
        <div id="message-container"></div>
    </div>
</div>

@endsection

@push('after_styles')
<style>
    /* Enhanced responsive table container */
    .table-responsive {
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        position: relative;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #0d6efd #f8f9fa;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 6px;
        border: 2px solid #f8f9fa;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #0a58ca;
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 200;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    #attendance-table {
        border-collapse: separate;
        border-spacing: 0;
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }
    
    #attendance-table th {
        border: none;
        text-align: center;
        font-weight: 700;
        padding: 16px 12px;
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        white-space: nowrap;
        position: relative;
    }
    
    #attendance-table th::after {
        content: '';
        position: absolute;
        right: 0;
        top: 20%;
        height: 60%;
        width: 1px;
        background: rgba(255,255,255,0.3);
    }
    
    #attendance-table td {
        border-bottom: 1px solid #e9ecef;
        border-right: 1px solid #e9ecef;
        padding: 12px 8px;
        text-align: center;
        vertical-align: middle;
        transition: all 0.2s ease;
    }
    
    #attendance-table tbody tr:hover {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 110, 253, 0.02) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .day-header {
        writing-mode: horizontal-tb;
        text-orientation: mixed;
        min-width: 160px;
        max-width: 160px;
        text-align: center;
        font-size: 0.85rem;
        padding: 16px 12px;
        font-weight: 700;
        line-height: 1.3;
        color: white;
    }
    
    .weekend-header {
        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%) !important;
        color: white !important;
    }
    
    .day-cell {
        min-width: 160px;
        max-width: 160px;
        text-align: center;
        vertical-align: top;
        padding: 16px 12px;
        position: relative;
        border: 1px solid #e9ecef;
        background: white;
    }
    
    .weekend-cell {
        background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%);
        border-color: #f8d7da;
    }
    
    .employee-name-cell {
        min-width: 240px;
        max-width: 240px;
        text-align: left !important;
        font-weight: 700;
        padding: 16px !important;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        position: sticky;
        left: 0;
        z-index: 150;
        border-right: 3px solid #0d6efd;
        box-shadow: 4px 0 8px rgba(0,0,0,0.1);
        color: #495057;
        border-left: 4px solid #28a745;
    }
    
    .attendance-day-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        min-height: 90px;
        width: 100%;
        padding: 8px 4px;
    }
    
    .shift-checkboxes {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        width: 100%;
    }
    
    .shift-check {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 6px 10px;
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
        border-radius: 8px;
        width: 95%;
        min-height: 28px;
        border: 1px solid rgba(13, 110, 253, 0.2);
        transition: all 0.2s ease;
    }
    
    .shift-check:hover {
        background: linear-gradient(135deg, rgba(13, 110, 253, 0.15) 0%, rgba(13, 110, 253, 0.08) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .shift-checkbox {
        margin: 0 8px 0 0;
        cursor: pointer;
        transform: scale(1.2);
        position: relative;
        accent-color: #0d6efd;
    }
    
    .shift-checkbox:checked {
        filter: brightness(1.1);
    }
    
    .shift-label {
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0;
        cursor: pointer;
        color: #0d6efd;
        min-width: 24px;
        text-align: center;
        letter-spacing: 0.5px;
    }
    
    .ot-checkbox {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        padding: 6px 8px;
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.15) 0%, rgba(255, 193, 7, 0.08) 100%);
        border-radius: 8px;
        width: 95%;
        min-height: 26px;
        border: 1px solid rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    
    .ot-checkbox:hover {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.2) 0%, rgba(255, 193, 7, 0.1) 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .ot-input {
        margin: 0 6px 0 0;
        cursor: pointer;
        transform: scale(1.15);
        accent-color: #ffc107;
    }
    
    .ot-input:checked {
        filter: brightness(1.1);
    }
    
    .ot-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #856404;
        cursor: pointer;
        margin-bottom: 0;
        text-align: center;
        min-width: 20px;
        letter-spacing: 0.3px;
    }
    
    .total-cell {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        font-weight: 700;
        min-width: 100px;
        text-align: center;
        vertical-align: middle;
        font-size: 0.9rem;
        padding: 12px 8px;
        color: #495057;
        border: 2px solid #adb5bd;
    }
    
    /* Comprehensive responsive breakpoints */
    @media (max-width: 1400px) {
        .day-header, .day-cell {
            min-width: 140px;
            max-width: 140px;
            padding: 14px 10px;
        }
        
        .employee-name-cell {
            min-width: 180px;
            max-width: 180px;
        }
    }
    
    @media (max-width: 1200px) {
        .day-header, .day-cell {
            min-width: 120px;
            max-width: 120px;
            padding: 12px 8px;
            font-size: 0.8rem;
        }
        
        .employee-name-cell {
            min-width: 160px;
            max-width: 160px;
            font-size: 0.9rem;
        }
        
        .attendance-day-container {
            min-height: 85px;
            gap: 8px;
            padding: 6px 3px;
        }
        
        .shift-check, .ot-checkbox {
            min-height: 26px;
            padding: 5px 7px;
        }
    }
    
    @media (max-width: 992px) {
        .day-header, .day-cell {
            min-width: 100px;
            max-width: 100px;
            padding: 10px 6px;
            font-size: 0.75rem;
        }
        
        .employee-name-cell {
            min-width: 140px;
            max-width: 140px;
            font-size: 0.85rem;
            padding: 12px 8px !important;
        }
        
        #attendance-table {
            font-size: 0.8rem;
        }
        
        .attendance-day-container {
            min-height: 80px;
            gap: 6px;
        }
        
        .shift-label {
            font-size: 0.8rem;
        }
        
        .ot-label {
            font-size: 0.7rem;
        }
    }
    
    @media (max-width: 768px) {
        .day-header, .day-cell {
            min-width: 85px;
            max-width: 85px;
            padding: 8px 4px;
            font-size: 0.7rem;
        }
        
        .employee-name-cell {
            min-width: 120px;
            max-width: 120px;
            font-size: 0.8rem;
            padding: 10px 6px !important;
        }
        
        .attendance-day-container {
            min-height: 75px;
            gap: 4px;
            padding: 4px 2px;
        }
        
        .shift-check, .ot-checkbox {
            min-height: 22px;
            padding: 3px 5px;
            width: 98%;
        }
        
        .shift-checkbox {
            transform: scale(1.0);
        }
        
        .ot-input {
            transform: scale(1.0);
        }
        
        .table-responsive {
            max-height: 60vh;
        }
        
        #attendance-table {
            font-size: 0.75rem;
        }
    }
    
    @media (max-width: 576px) {
        .day-header, .day-cell {
            min-width: 75px;
            max-width: 75px;
            padding: 6px 3px;
            font-size: 0.65rem;
        }
        
        .employee-name-cell {
            min-width: 100px;
            max-width: 100px;
            font-size: 0.75rem;
            padding: 8px 4px !important;
        }
        
        .attendance-day-container {
            min-height: 70px;
            gap: 3px;
            padding: 3px 1px;
        }
        
        .shift-check, .ot-checkbox {
            min-height: 20px;
            padding: 2px 4px;
        }
        
        .shift-label, .ot-label {
            font-size: 0.6rem;
        }
        
        .table-responsive {
            max-height: 55vh;
        }
        
        #attendance-table {
            font-size: 0.7rem;
        }
        
        #attendance-table th {
            padding: 12px 8px;
        }
    }
    
    .bulk-check {
        margin: 3px;
        font-size: 0.85rem;
        padding: 6px 12px;
    }

    /* Ensure proper table layout */
    #attendance-table {
        table-layout: fixed;
        width: 100%;
        border-spacing: 0;
        border-collapse: separate;
    }
    
    #attendance-table th,
    #attendance-table td {
        border: 1px solid #dee2e6;
        padding: 8px;
        vertical-align: middle;
    }
    
    #attendance-table thead th {
        background-color: #212529 !important;
        color: white !important;
        font-weight: bold;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    #attendance-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    #attendance-table tbody tr:hover {
        background-color: #e9ecef;
    }
    
    /* Better form check styling */
    .form-check {
        margin-bottom: 0;
        min-height: auto;
    }
    
    .form-check-input {
        margin-top: 0;
        border-width: 2px;
    }
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .ot-input:checked {
        background-color: #fd7e14;
        border-color: #fd7e14;
        box-shadow: 0 0 0 0.2rem rgba(253, 126, 20, 0.25);
    }
    
    .form-check-label {
        margin-bottom: 0;
        line-height: 1.2;
        font-weight: 600;
    }
    
    /* Improve day header appearance */
    .day-header div {
        margin: 2px 0;
    }
    
    .day-header .small {
        font-size: 0.7rem;
        opacity: 0.8;
    }
    
    /* Existing attendance styling */
    .existing-attendance.shift-checkbox:checked {
        background-color: #20c997 !important;
        border-color: #20c997 !important;
        box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25) !important;
    }
    
    .existing-attendance.ot-input:checked {
        background-color: #17a2b8 !important;
        border-color: #17a2b8 !important;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25) !important;
    }
    
    .existing-attendance + .form-check-label {
        color: #20c997 !important;
        font-weight: 700 !important;
    }
    
    /* Mobile responsive behavior */
    @media (max-width: 768px) {
        .day-header, .day-cell {
            min-width: 90px;
            max-width: 90px;
            padding: 8px 4px !important;
        }
        
        .employee-name {
            min-width: 120px;
            max-width: 120px;
            font-size: 0.8rem;
            padding: 8px !important;
        }
        
        .attendance-day-container {
            min-height: 70px;
            gap: 4px;
            padding: 4px 2px;
        }
        
        .shift-check, .ot-checkbox {
            min-height: 22px;
            padding: 4px 6px;
            width: 98%;
        }
        
        .shift-label {
            font-size: 0.7rem;
        }
        
        .ot-checkbox label {
            font-size: 0.65rem;
        }
        
        #attendance-table {
            font-size: 0.75rem;
        }
        
        .table-responsive {
            max-height: 60vh;
        }
    }
    
    @media (max-width: 576px) {
        .day-header, .day-cell {
            min-width: 80px;
            max-width: 80px;
            padding: 6px 2px !important;
        }
        
        .employee-name {
            min-width: 100px;
            max-width: 100px;
            font-size: 0.75rem;
        }
        
        .attendance-day-container {
            min-height: 65px;
            gap: 3px;
        }
        
        .shift-check, .ot-checkbox {
            min-height: 20px;
            padding: 3px 4px;
        }
        
        .shift-checkbox {
            transform: scale(1.0);
        }
    }

    /* Enhanced table behavior */
    #attendance-table th,
    #attendance-table td {
        white-space: nowrap;
    }
    
    /* Smooth scrolling */
    .table-responsive {
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #0d6efd #f8f9fa;
    }
    
    .table-responsive::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #0d6efd;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #0a58ca;
    }
</style>
@endpush

@push('after_scripts')
<script>
$(document).ready(function() {
    console.log('=== BULK ATTENDANCE PAGE LOADED ===');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Current URL:', window.location.href);
    console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));
    
    // Test bulk action buttons are present
    console.log('Bulk action buttons found:', $('.bulk-check').length);
    $('.bulk-check').each(function(index, element) {
        console.log(`Button ${index + 1}:`, $(element).text(), 'Action:', $(element).data('action'));
    });
    
    // Test event handler binding immediately
    setTimeout(() => {
        console.log('=== TESTING EVENT BINDING ===');
        const testButton = $('.bulk-check[data-action="all-days"]');
        console.log('All Days button found:', testButton.length);
        
        if (testButton.length > 0) {
            console.log('Adding test click handler...');
            testButton.off('click.test').on('click.test', function() {
                console.log('🎉 EVENT HANDLER WORKING! Button clicked:', $(this).text());
            });
        }
    }, 1000);
    
    // Global variables
    let currentData = {};
    let calendar = [];

    // Add test function to global scope for manual testing
    window.testBulkActions = function() {
        console.log('=== TESTING BULK ACTIONS ===');
        console.log('Bulk buttons:', $('.bulk-check').length);
        console.log('Shift checkboxes:', $('.shift-checkbox').length);
        console.log('OT inputs:', $('.ot-input').length);
        
        if ($('.shift-checkbox').length === 0) {
            console.log('❌ No checkboxes found - need to search employees first');
            return false;
        }
        
        console.log('✅ Elements found, testing "All Days" action...');
        $('.bulk-check[data-action="all-days"]').click();
        return true;
    };

    // Add weekend testing function  
    window.testWeekendLogic = function() {
        console.log('=== TESTING WEEKEND LOGIC ===');
        $('.shift-checkbox[data-shift="1"]').each(function(index, element) {
            const dateString = $(element).data('date');
            if (dateString) {
                console.log('Processing date:', dateString);
                
                // Test different parsing methods
                const date1 = new Date(dateString);
                const date2 = new Date(dateString + 'T00:00:00');
                const date3 = new Date(dateString + 'T12:00:00');
                
                console.log('Method 1 (direct):', date1, 'Day:', date1.getDay());
                console.log('Method 2 (midnight):', date2, 'Day:', date2.getDay()); 
                console.log('Method 3 (noon):', date3, 'Day:', date3.getDay());
                console.log('---');
            }
        });
    };

    // Direct test functions that bypass event handlers
    window.directTestAllDays = function() {
        console.log('🧪 DIRECT TEST: All Days - S1 Shift Only');
        
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const s1Checkboxes = $('.shift-checkbox[data-shift="1"]');
        console.log('Found S1 checkboxes:', s1Checkboxes.length);
        
        if (s1Checkboxes.length === 0) {
            alert('❌ No S1 checkboxes found! Search for employees first.');
            return;
        }
        
        s1Checkboxes.prop('checked', true);
        console.log('✅ All S1 checkboxes checked for all days');
        alert(`✅ All Days: Checked ${s1Checkboxes.length} S1 shift checkboxes`);
    };

    window.directTestExceptSunday = function() {
        console.log('🧪 DIRECT TEST: Except Sunday - S1 Only');
        
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const s1Checkboxes = $('.shift-checkbox[data-shift="1"]');
        console.log('Found S1 checkboxes:', s1Checkboxes.length);
        
        if (s1Checkboxes.length === 0) {
            alert('❌ No S1 checkboxes found! Search for employees first.');
            return;
        }

        let checkedCount = 0;
        s1Checkboxes.each(function() {
            const dateString = $(this).data('date');
            if (dateString) {
                const date = new Date(dateString + 'T12:00:00');
                const dayOfWeek = date.getDay();
                console.log('Date:', dateString, 'Day:', dayOfWeek, 'Is Sunday:', dayOfWeek === 0);
                if (dayOfWeek !== 0) { // Not Sunday
                    $(this).prop('checked', true);
                    checkedCount++;
                }
            }
        });
        
        console.log('✅ Checked', checkedCount, 'non-Sunday S1 checkboxes');
        alert(`✅ Except Sunday: Checked ${checkedCount} S1 shift checkboxes (excluding Sundays)`);
    };

    window.directTestClear = function() {
        console.log('🧪 DIRECT TEST: Clear All');
        const checkboxes = $('.shift-checkbox, .ot-input');
        console.log('Found checkboxes:', checkboxes.length);
        
        checkboxes.prop('checked', false);
        console.log('✅ All checkboxes cleared');
        alert(`✅ Cleared ${checkboxes.length} checkboxes`);
    };

    // Additional direct test functions
    window.directTestExceptSaturday = function() {
        console.log('🧪 DIRECT TEST: Except Saturday - S1 Only');
        
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const s1Checkboxes = $('.shift-checkbox[data-shift="1"]');
        if (s1Checkboxes.length === 0) {
            alert('❌ No S1 checkboxes found! Search for employees first.');
            return;
        }
        let checkedCount = 0;
        s1Checkboxes.each(function() {
            const dateString = $(this).data('date');
            if (dateString) {
                const date = new Date(dateString + 'T12:00:00');
                const dayOfWeek = date.getDay();
                console.log('Date:', dateString, 'Day:', dayOfWeek, 'Is Saturday:', dayOfWeek === 6);
                if (dayOfWeek !== 6) { // Not Saturday
                    $(this).prop('checked', true);
                    checkedCount++;
                }
            }
        });
        console.log('✅ Checked', checkedCount, 'non-Saturday S1 checkboxes');
        alert(`✅ Except Saturday: Checked ${checkedCount} S1 shift checkboxes (excluding Saturdays)`);
    };

    window.directTestExceptWeekend = function() {
        console.log('🧪 DIRECT TEST: Except Weekend (Monday-Friday) - S1 Only');
        
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const s1Checkboxes = $('.shift-checkbox[data-shift="1"]');
        if (s1Checkboxes.length === 0) {
            alert('❌ No S1 checkboxes found! Search for employees first.');
            return;
        }
        let checkedCount = 0;
        s1Checkboxes.each(function() {
            const dateString = $(this).data('date');
            if (dateString) {
                const date = new Date(dateString + 'T12:00:00');
                const dayOfWeek = date.getDay();
                console.log('Date:', dateString, 'Day:', dayOfWeek, 'Is Weekend:', (dayOfWeek === 0 || dayOfWeek === 6));
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not weekend (Monday-Friday)
                    $(this).prop('checked', true);
                    checkedCount++;
                }
            }
        });
        console.log('✅ Checked', checkedCount, 'weekday S1 checkboxes');
        alert(`✅ Monday-Friday only: Checked ${checkedCount} S1 shift checkboxes`);
    };

    window.directTestShift1 = function() {
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const checkboxes = $('.shift-checkbox[data-shift="1"]');
        checkboxes.prop('checked', true);
        alert(`✅ S1 All Days: Checked ${checkboxes.length} checkboxes`);
    };

    window.directTestShift2 = function() {
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const checkboxes = $('.shift-checkbox[data-shift="2"]');
        checkboxes.prop('checked', true);
        alert(`✅ S2 All Days: Checked ${checkboxes.length} checkboxes`);
    };

    window.directTestShift3 = function() {
        // Clear all checkboxes first
        clearAllCheckboxes();
        
        const checkboxes = $('.shift-checkbox[data-shift="3"]');
        checkboxes.prop('checked', true);
        alert(`✅ S3 All Days: Checked ${checkboxes.length} checkboxes`);
    };

    // Update hidden month field when month/year selectors change
    $('#month_select, #year_select').on('change', function() {
        const selectedMonth = $('#month_select').val();
        const selectedYear = $('#year_select').val();
        const monthValue = selectedYear + '-' + selectedMonth;
        $('#month').val(monthValue);
        console.log('Month/Year changed to:', monthValue);
    });

    // Search form submission - SINGLE handler inside document.ready
    $('#attendance-search-form').off('submit').on('submit', function(e) {
        console.log('=== FORM SUBMISSION HANDLER ===');
        e.preventDefault();
        e.stopImmediatePropagation();
        
        console.log('Form submission prevented, processing via AJAX...');
        
        // Store current form values before search
        const currentFormValues = {
            site_id: $('#site_id').val(),
            user_type: $('#user_type').val(),
            month_select: $('#month_select').val(),
            year_select: $('#year_select').val(),
            shifts: $('input[name="shifts[]"]:checked').map(function() {
                return this.value;
            }).get()
        };
        
        console.log('Current form values before search:', currentFormValues);
        
        // Combine month and year into proper format
        const selectedMonth = $('#month_select').val();
        const selectedYear = $('#year_select').val();
        const monthValue = selectedYear + '-' + selectedMonth;
        $('#month').val(monthValue);
        
        const formData = {
            site_id: $('#site_id').val(),
            user_type: $('#user_type').val(),
            month: monthValue,
            shifts: $('input[name="shifts[]"]:checked').map(function() {
                return this.value;
            }).get()
        };

        console.log('Form data being sent:', formData);

        if (!formData.site_id || !formData.user_type || !formData.month) {
            console.log('Validation failed: missing required fields');
            showMessage('error', 'Please fill all required fields.');
            return false;
        }

        if (formData.shifts.length === 0) {
            console.log('Validation failed: no shifts selected');
            showMessage('error', 'Please select at least one shift.');
            return false;
        }

        console.log('=== VALIDATION PASSED - CALLING SEARCH ===');
        // Call search with preserved values
        searchEmployees(formData, currentFormValues);
        return false; // Prevent any form submission
    });

    // Search employees function
    function searchEmployees(formData, preserveValues = null) {
        console.log('=== SEARCH EMPLOYEES FUNCTION ===');
        console.log('Form data received:', formData);
        console.log('Preserve values:', preserveValues);

        $('#loading-spinner').show();

        $.ajax({
            url: '{{ route("admin.bulk-attendance.search") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('=== SEARCH SUCCESS ===');
                console.log('Response received:', response);
                
                $('#loading-spinner').hide();
                
                // Validate response structure
                if (!response || !response.data) {
                    console.error('Invalid response structure:', response);
                    showMessage('error', 'Invalid response from server');
                    return;
                }

                const { data } = response;
                console.log('Processing data:', data);

                // Store current response data globally
                currentData = data;

                // Restore form values if provided
                if (preserveValues) {
                    console.log('=== RESTORING FORM VALUES ===');
                    console.log('Values to restore:', preserveValues);
                    
                    // Restore select fields
                    if (preserveValues.site_id) {
                        $('#site_id').val(preserveValues.site_id);
                        console.log('Restored site_id to:', preserveValues.site_id);
                    }
                    
                    if (preserveValues.user_type) {
                        $('#user_type').val(preserveValues.user_type);
                        console.log('Restored user_type to:', preserveValues.user_type);
                    }
                    
                    if (preserveValues.month_select) {
                        $('#month_select').val(preserveValues.month_select);
                        console.log('Restored month_select to:', preserveValues.month_select);
                    }
                    
                    if (preserveValues.year_select) {
                        $('#year_select').val(preserveValues.year_select);
                        console.log('Restored year_select to:', preserveValues.year_select);
                    }
                    
                    // Restore checkboxes
                    $('input[name="shifts[]"]').prop('checked', false);
                    if (preserveValues.shifts && preserveValues.shifts.length > 0) {
                        preserveValues.shifts.forEach(function(shift) {
                            $('input[name="shifts[]"][value="' + shift + '"]').prop('checked', true);
                            console.log('Restored shift checkbox:', shift);
                        });
                    }
                    
                    console.log('=== FORM VALUES RESTORATION COMPLETE ===');
                }

                // Build table
                buildAttendanceTable(data);
                
                // Show attendance section
                $('#attendance-section').show();
                
                // Enable allocate button if employees exist
                if (data.employees && data.employees.length > 0) {
                    $('#allocate-btn').prop('disabled', false);
                    showMessage('success', `Found ${data.employees.length} employees`);
                } else {
                    $('#allocate-btn').prop('disabled', true);
                    showMessage('warning', 'No employees found for the selected criteria');
                }
            },
            error: function(xhr, status, error) {
                console.error('=== SEARCH ERROR ===');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response text:', xhr.responseText);
                
                $('#loading-spinner').hide();
                
                let errorMessage = 'Search failed';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    console.error('Could not parse error response as JSON');
                    errorMessage = 'Server error: ' + status;
                }
                
                showMessage('error', errorMessage);
            }
        });
    }

    // Build attendance table
    function buildAttendanceTable(data) {
        const { employees, calendar, site, existing_attendance, master } = data;
        window.masterRecord = master || null;
        
        console.log('=== BUILD TABLE DEBUG ===');
        console.log('Employees received:', employees);
        console.log('Calendar received:', calendar);
        console.log('Site received:', site);
        console.log('Existing attendance:', existing_attendance);
        console.log('Existing attendance keys:', Object.keys(existing_attendance));
        console.log('Sample existing attendance data:', Object.keys(existing_attendance).slice(0, 3).map(key => ({
            key: key,
            data: existing_attendance[key]
        })));
        
        // Check if there's existing attendance data
        const hasExistingData = Object.keys(existing_attendance).length > 0;
        
        // Store mode for later use
        window.attendanceMode = hasExistingData ? 'edit' : 'create';
        
        console.log('Building table with:', {
            employees: employees.length,
            calendar: calendar.length,
            site: site.name,
            hasExistingData: hasExistingData
        });
        
        // Show status + mode message
        const status = master && master.status ? master.status : (hasExistingData ? 'submitted' : 'draft');
        let statusBadge = '';
        if (status === 'draft') statusBadge = '<span class="badge bg-secondary">Draft</span>';
        if (status === 'submitted') statusBadge = '<span class="badge bg-info">Submitted</span>';
        if (status === 'approved') statusBadge = '<span class="badge bg-success">Approved</span>';
        if (status === 'locked') statusBadge = '<span class="badge bg-dark">Locked</span>';

        // Show edit mode message if there's existing data
        if (hasExistingData) {
            const existingCount = Object.keys(existing_attendance).length;
            $('#attendance-info').html(`
                <div class="alert alert-info mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="la la-edit"></i> <strong>Edit Mode:</strong> Attendance for <strong>${site.name}</strong> · ${statusBadge}
                            <br><small><i class="la la-info-circle"></i> Found <strong>${existingCount}</strong> existing attendance records. You can modify and resubmit.</small>
                        </div>
                        <div>
                            ${master && master.status === 'draft' ? '<button class="btn btn-sm btn-outline-primary" id="btn-submit"><i class="la la-paper-plane"></i> Submit</button>' : ''}
                            ${master && master.status === 'submitted' ? '<button class="btn btn-sm btn-outline-success ms-1" id="btn-approve"><i class="la la-check"></i> Approve</button>' : ''}
                            ${master && master.status === 'approved' ? '<button class="btn btn-sm btn-outline-dark ms-1" id="btn-lock"><i class="la la-lock"></i> Lock</button>' : ''}
                            ${master ? '<a class="btn btn-sm btn-success ms-1" target="_blank" href="/admin/bulk-attendance/'+master.id+'/export.csv"><i class=\"la la-download\"></i> CSV</a>' : ''}
                        </div>
                    </div>
                </div>
            `);
            $('#submit-attendance-btn').html('<i class="la la-save"></i> Update Attendance');
            $('#edit-mode-controls').show(); // Show edit controls
            if (master && master.status === 'locked') {
                $('#submit-attendance-btn').prop('disabled', true).text('Locked');
            }
        } else {
            $('#attendance-info').html(`
                <div class="alert alert-success mb-3">
                    <i class="la la-plus-circle"></i> <strong>Create Mode:</strong> 
                    Creating new attendance records for <strong>${site.name}</strong> in the selected month.
                </div>
            `);
            $('#submit-attendance-btn').html('<i class="la la-save"></i> Submit Full Month Attendance');
            $('#edit-mode-controls').hide(); // Hide edit controls
        }
        
        if (employees.length === 0) {
            console.log('No employees, showing empty message');
            $('#attendance-tbody').html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="la la-exclamation-triangle" style="font-size: 2rem; color: #ffc107;"></i>
                        <h4>No employees found</h4>
                        <p>No employees found for the selected designation. Try a different user type.</p>
                    </td>
                </tr>
            `);
            $('#table-head').html(`
                <tr>
                    <th>Sr. No</th>
                    <th>Site Name</th>
                    <th>Employee</th>
                    <th>User Type</th>
                    <th>Days</th>
                    <th>Total</th>
                </tr>
            `);
            return;
        }
        
        // Build complete header row
        let headerHtml = `
            <tr>
                <th style="min-width: 80px; padding: 12px 8px; text-align: center;">Sr. No</th>
                <th style="min-width: 180px; padding: 12px 8px; text-align: center;">Site Name</th>
                <th style="min-width: 220px; padding: 12px 8px; text-align: center;">👤 Employee Details</th>
                <th style="min-width: 150px; padding: 12px 8px; text-align: center;">Designation</th>
        `;
        
        calendar.forEach(day => {
            const isWeekend = day.is_weekend;
            const weekendClass = isWeekend ? 'weekend-header' : '';
            headerHtml += `
                <th class="day-header ${weekendClass}">
                    <div>Day ${day.day}</div>
                    <div class="small">${day.day_name}</div>
                    <div class="small">Total: <span id="day-total-${day.day}">0</span></div>
                </th>
            `;
        });
        
        headerHtml += `
                <th>Total</th>
            </tr>
        `;
        
        $('#table-head').html(headerHtml);
        
        // Build rows
        let bodyHtml = '';
        console.log('=== BUILDING EMPLOYEE ROWS ===');
        console.log('Employees data:', employees);
        
        employees.forEach((employee, index) => {
            console.log(`Employee ${index + 1}:`, {
                id: employee.id,
                name: employee.name,
                first_name: employee.first_name,
                last_name: employee.last_name,
                emp_id: employee.emp_id,
                designation: employee.designation,
                full_object: employee
            });
            
            const employeeKey = `${employee.id}`;
            let rowHtml = `
                <tr data-employee-id="${employee.id}" style="min-height: 100px;">
                    <td style="min-width: 80px; text-align: center; padding: 12px 8px; font-weight: bold; font-size: 0.9rem;">${index + 1}</td>
                    <td style="min-width: 180px; text-align: center; padding: 12px 8px; font-size: 0.85rem;">${site.name}</td>
                    <td class="employee-name-cell">
                        <div style="text-align: left; line-height: 1.4; padding: 8px;">
                            <div style="font-size: 1.1rem; font-weight: 700; color: #0d6efd; margin-bottom: 3px;">
                                👤 ${employee.name && employee.name.trim() !== '' ? employee.name : 'No Name Available'}
                            </div>
                            <div style="color: #666; font-size: 0.85rem; margin-bottom: 2px;">
                                🆔 ID: ${employee.emp_id || 'N/A'}
                            </div>
                            <div style="color: #888; font-size: 0.8rem;">
                                💼 ${employee.designation || 'N/A'}
                            </div>
                        </div>
                    </td>
                    <td style="min-width: 150px; text-align: center; padding: 12px 8px; font-size: 0.85rem;">${employee.designation || 'N/A'}</td>
            `;
            
            let monthTotal = 0;
            
            calendar.forEach(day => {
                const dayKey = `${employee.id}-${day.date}`;
                const isWeekend = day.is_weekend;
                const weekendClass = isWeekend ? 'weekend-cell' : '';
                
                // Check if there's existing attendance
                const existingValue = existing_attendance[dayKey] || '';
                
                // Generate shift checkboxes and OT for each day
                const dayAttendance = existing_attendance[dayKey] || {};
                
                // Debug existing attendance for first few records
                if (index < 2 && Object.keys(existing_attendance).length > 0) {
                    console.log(`🔍 Employee ${employee.id}, Date ${day.date}:`, {
                        dayKey: dayKey,
                        dayAttendance: dayAttendance,
                        hasShift1: !!dayAttendance.shift_1,
                        hasShift2: !!dayAttendance.shift_2,
                        hasShift3: !!dayAttendance.shift_3,
                        hasOT: !!dayAttendance.ot,
                        willCheckS1: dayAttendance.shift_1 ? 'YES' : 'NO',
                        willCheckS2: dayAttendance.shift_2 ? 'YES' : 'NO',
                        willCheckS3: dayAttendance.shift_3 ? 'YES' : 'NO',
                        willCheckOT: dayAttendance.ot ? 'YES' : 'NO'
                    });
                }
                
                rowHtml += `
                    <td class="day-cell ${weekendClass}">
                        <div class="attendance-day-container">
                            <!-- Shift Checkboxes - Vertical Layout -->
                            <div class="shift-checkboxes">
                                <div class="form-check shift-check">
                                    <input class="form-check-input shift-checkbox ${dayAttendance.shift_1 ? 'existing-attendance' : ''}" type="checkbox" 
                                           name="attendance[${employee.id}][${day.date}][shift_1]" 
                                           value="1" id="shift1_${employee.id}_${day.date}"
                                           data-employee-id="${employee.id}" 
                                           data-date="${day.date}"
                                           data-day="${day.day}" data-shift="1"
                                           ${dayAttendance.shift_1 ? 'checked' : ''}>
                                    <label class="form-check-label shift-label" for="shift1_${employee.id}_${day.date}">S1</label>
                                </div>
                                <div class="form-check shift-check">
                                    <input class="form-check-input shift-checkbox ${dayAttendance.shift_2 ? 'existing-attendance' : ''}" type="checkbox" 
                                           name="attendance[${employee.id}][${day.date}][shift_2]" 
                                           value="1" id="shift2_${employee.id}_${day.date}"
                                           data-employee-id="${employee.id}" 
                                           data-date="${day.date}"
                                           data-day="${day.day}" data-shift="2"
                                           ${dayAttendance.shift_2 ? 'checked' : ''}>
                                    <label class="form-check-label shift-label" for="shift2_${employee.id}_${day.date}">S2</label>
                                </div>
                                <div class="form-check shift-check">
                                    <input class="form-check-input shift-checkbox ${dayAttendance.shift_3 ? 'existing-attendance' : ''}" type="checkbox" 
                                           name="attendance[${employee.id}][${day.date}][shift_3]" 
                                           value="1" id="shift3_${employee.id}_${day.date}"
                                           data-employee-id="${employee.id}" 
                                           data-date="${day.date}"
                                           data-day="${day.day}" data-shift="3"
                                           ${dayAttendance.shift_3 ? 'checked' : ''}>
                                    <label class="form-check-label shift-label" for="shift3_${employee.id}_${day.date}">S3</label>
                                </div>
                            </div>
                            <!-- OT Checkbox -->
                            <div class="ot-checkbox">
                                <div class="form-check">
                                    <input class="form-check-input ot-input ${dayAttendance.ot ? 'existing-attendance' : ''}" type="checkbox" 
                                           name="attendance[${employee.id}][${day.date}][ot]" 
                                           value="1" id="ot_${employee.id}_${day.date}"
                                           data-employee-id="${employee.id}" 
                                           data-date="${day.date}"
                                           data-day="${day.day}"
                                           ${dayAttendance.ot ? 'checked' : ''}>
                                    <label class="form-check-label ot-label" for="ot_${employee.id}_${day.date}">OT</label>
                                </div>
                            </div>
                        </div>
                    </td>
                `;
                
                // Count total shifts for this employee on this day
                let dayShifts = 0;
                if (dayAttendance.shift_1) dayShifts++;
                if (dayAttendance.shift_2) dayShifts++;
                if (dayAttendance.shift_3) dayShifts++;
                if (dayAttendance.ot) dayShifts += 0.5; // OT counts as 0.5
                
                monthTotal += dayShifts;
            });
            
            rowHtml += `
                    <td class="total-cell">
                        <strong class="monthly-total" id="total-${employee.id}">${monthTotal.toFixed(1)}</strong>
                    </td>
                </tr>
            `;
            
            bodyHtml += rowHtml;
        });
        
        $('#attendance-tbody').html(bodyHtml);
        
        // Store calendar globally (use window to access global scope)
        window.calendar = calendar;
        
        // Verify existing attendance is properly displayed
        if (hasExistingData) {
            // Ensure any missed checks are applied based on existing_attendance
            try { window.verifyExistingAttendance && window.verifyExistingAttendance(); } catch(e) { console.warn('verifyExistingAttendance error', e); }
            setTimeout(() => {
                const checkedCheckboxes = $('.shift-checkbox:checked, .ot-input:checked').length;
                const existingAttendanceCheckboxes = $('.existing-attendance:checked').length;
                console.log('📋 Existing Attendance Verification:', {
                    totalCheckedCheckboxes: checkedCheckboxes,
                    existingAttendanceCheckboxes: existingAttendanceCheckboxes,
                    existingDataKeys: Object.keys(existing_attendance).length,
                    sampleCheckedIds: $('.shift-checkbox:checked, .ot-input:checked').slice(0, 5).map((i, el) => el.id).get()
                });
                if (checkedCheckboxes > 0) {
                    console.log('✅ Existing attendance successfully displayed as checked checkboxes');
                } else {
                    console.warn('⚠️ No checkboxes are checked despite having existing attendance data');
                }
            }, 100);
        }
        
        // Recalculate totals
        updateAllTotals();
        
        // Bind input change events for shift checkboxes and OT
        $('.shift-checkbox, .ot-input').on('change', function() {
            const employeeId = $(this).data('employee-id');
            const day = $(this).data('day');
            
            updateEmployeeTotal(employeeId);
            updateDayTotal(day);
        });
        
        console.log('Table built successfully');
    }

    // Function to clear all checkboxes before applying new bulk action
    function clearAllCheckboxes() {
        console.log('🧹 Clearing all checkboxes before new bulk action...');
        $('.shift-checkbox, .ot-input').prop('checked', false);
        console.log('✅ All checkboxes cleared');
    }
    
    // Function to verify and fix existing attendance display
    window.verifyExistingAttendance = function() {
        console.log('🔍 Verifying existing attendance display...');
        if (!currentData || !currentData.existing_attendance) {
            console.log('❌ No existing attendance data found');
            return false;
        }
        
        const existing = currentData.existing_attendance;
        let fixedCount = 0;
        
        Object.keys(existing).forEach(key => {
            const attendance = existing[key];
            const sep = key.indexOf('-');
            const employeeId = key.substring(0, sep);
            const date = key.substring(sep + 1);
            
            // Check and fix S1
            if (attendance.shift_1) {
                const s1Checkbox = $(`#shift1_${employeeId}_${date}`);
                if (s1Checkbox.length && !s1Checkbox.is(':checked')) {
                    s1Checkbox.prop('checked', true);
                    fixedCount++;
                }
            }
            
            // Check and fix S2
            if (attendance.shift_2) {
                const s2Checkbox = $(`#shift2_${employeeId}_${date}`);
                if (s2Checkbox.length && !s2Checkbox.is(':checked')) {
                    s2Checkbox.prop('checked', true);
                    fixedCount++;
                }
            }
            
            // Check and fix S3
            if (attendance.shift_3) {
                const s3Checkbox = $(`#shift3_${employeeId}_${date}`);
                if (s3Checkbox.length && !s3Checkbox.is(':checked')) {
                    s3Checkbox.prop('checked', true);
                    fixedCount++;
                }
            }
            
            // Check and fix OT
            if (attendance.ot) {
                const otCheckbox = $(`#ot_${employeeId}_${date}`);
                if (otCheckbox.length && !otCheckbox.is(':checked')) {
                    otCheckbox.prop('checked', true);
                    fixedCount++;
                }
            }
        });
        
        console.log(`✅ Verification complete. Fixed ${fixedCount} checkboxes.`);
        if (fixedCount > 0) {
            updateAllTotals();
        }
        return fixedCount;
    };

    // Bulk check actions - bind outside of buildAttendanceTable
    $(document).on('click', '.bulk-check', function() {
        const action = $(this).data('action');
        console.log('=== BULK ACTION CLICKED ===');
        console.log('Button clicked:', $(this).text());
        console.log('Action data:', action);
        console.log('Available checkboxes:', $('.shift-checkbox').length);
        console.log('Available shift-1 checkboxes:', $('.shift-checkbox[data-shift="1"]').length);
        
        // Clear all checkboxes first (except for clear-all action which already does this)
        if (action !== 'clear-all') {
            clearAllCheckboxes();
        }
        
        switch(action) {
            case 'all-days':
                console.log('Processing: All Days');
                $('.shift-checkbox[data-shift="1"]').each(function(index, element) {
                    console.log('Setting checkbox:', $(element).attr('id'));
                    $(element).prop('checked', true).trigger('change');
                });
                break;
            case 'except-sunday':
                console.log('Processing: Except Sunday');
                $('.shift-checkbox[data-shift="1"]').each(function(index, element) {
                    const dateString = $(element).data('date');
                    console.log('Checking date string:', dateString);
                    if (dateString) {
                        const date = new Date(dateString + 'T00:00:00');
                        const dayOfWeek = date.getDay();
                        console.log('Date:', dateString, 'Day of week:', dayOfWeek, 'Is Sunday:', dayOfWeek === 0);
                        if (dayOfWeek !== 0) { // Not Sunday
                            $(element).prop('checked', true).trigger('change');
                        }
                    }
                });
                break;
            case 'except-saturday':
                console.log('Processing: Except Saturday');
                $('.shift-checkbox[data-shift="1"]').each(function(index, element) {
                    const dateString = $(element).data('date');
                    if (dateString) {
                        const date = new Date(dateString + 'T00:00:00');
                        const dayOfWeek = date.getDay();
                        console.log('Date:', dateString, 'Day of week:', dayOfWeek, 'Is Saturday:', dayOfWeek === 6);
                        if (dayOfWeek !== 6) { // Not Saturday
                            $(element).prop('checked', true).trigger('change');
                        }
                    }
                });
                break;
            case 'except-weekend':
                console.log('Processing: Except Weekend');
                $('.shift-checkbox[data-shift="1"]').each(function(index, element) {
                    const dateString = $(element).data('date');
                    if (dateString) {
                        const date = new Date(dateString + 'T00:00:00');
                        const dayOfWeek = date.getDay();
                        console.log('Date:', dateString, 'Day of week:', dayOfWeek, 'Is Weekend:', (dayOfWeek === 0 || dayOfWeek === 6));
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not weekend
                            $(element).prop('checked', true).trigger('change');
                        }
                    }
                });
                break;
            case 'shift-1':
                console.log('Processing: Shift 1');
                $('.shift-checkbox[data-shift="1"]').prop('checked', true).trigger('change');
                break;
            case 'shift-2':
                console.log('Processing: Shift 2');
                $('.shift-checkbox[data-shift="2"]').prop('checked', true).trigger('change');
                break;
            case 'shift-3':
                console.log('Processing: Shift 3');
                $('.shift-checkbox[data-shift="3"]').prop('checked', true).trigger('change');
                break;
            case 'clear-all':
                console.log('Processing: Clear All');
                $('.shift-checkbox, .ot-input').prop('checked', false).trigger('change');
                break;
            default:
                console.log('Unknown action:', action);
        }
        
        // Update all totals after bulk action
        setTimeout(() => {
            updateAllTotals();
            console.log(`✅ Bulk action "${action}" completed!`);
        }, 100);
        
        console.log('=== BULK ACTION COMPLETED ===');
    });

    // Real-time checkbox event handlers for totals
    $(document).on('change', '.shift-checkbox, .ot-input', function() {
        const employeeId = $(this).data('employee-id');
        const day = $(this).data('day');
        const date = $(this).data('date');
        
        console.log('Checkbox changed:', {
            checkbox: $(this).attr('class'),
            employeeId: employeeId,
            day: day,
            checked: $(this).is(':checked')
        });

        // Enforce mutually exclusive shift selection per employee/date
        if ($(this).hasClass('shift-checkbox') && $(this).is(':checked')) {
            const currentShift = $(this).data('shift');
            // Uncheck other shift checkboxes for same employee/date
            $(`.shift-checkbox[data-employee-id="${employeeId}"][data-date="${date}"]`).not(this).each(function() {
                if ($(this).is(':checked')) {
                    console.log('Unchecking conflicting shift', {
                        employeeId: employeeId,
                        date: date,
                        shift: $(this).data('shift'),
                        keptShift: currentShift
                    });
                    $(this).prop('checked', false);
                }
            });
        }
        
        // Update employee total
        updateEmployeeTotal(employeeId);
        
        // Update day total
        updateDayTotal(day);
    });

    // Update employee total to count shifts
    function updateEmployeeTotal(employeeId) {
        let total = 0;
        const shiftCount = $(`.shift-checkbox[data-employee-id="${employeeId}"]:checked`).length;
        const otCount = $(`.ot-input[data-employee-id="${employeeId}"]:checked`).length;
        
        total = shiftCount + (otCount * 0.5);
        
        const totalElement = $(`#total-${employeeId}`);
        if (totalElement.length) {
            totalElement.text(total.toFixed(1));
            console.log(`Updated employee ${employeeId} total: ${total.toFixed(1)} (${shiftCount} shifts + ${otCount} OT)`);
        }
    }

    // Update day total to count shifts
    function updateDayTotal(day) {
        let total = 0;
        const shiftCount = $(`.shift-checkbox[data-day="${day}"]:checked`).length;
        const otCount = $(`.ot-input[data-day="${day}"]:checked`).length;
        
        total = shiftCount + (otCount * 0.5);
        
        const totalElement = $(`#day-total-${day}`);
        if (totalElement.length) {
            totalElement.text(total.toFixed(1));
            console.log(`Updated day ${day} total: ${total.toFixed(1)} (${shiftCount} shifts + ${otCount} OT)`);
        }
    }

    // Update all totals
    function updateAllTotals() {
        console.log('Updating all totals...');
        
        // Get unique employee IDs
        const employeeIds = new Set();
        $('.shift-checkbox, .ot-input').each(function() {
            const employeeId = $(this).data('employee-id');
            if (employeeId) {
                employeeIds.add(employeeId);
            }
        });
        
        // Update employee totals
        employeeIds.forEach(employeeId => {
            updateEmployeeTotal(employeeId);
        });
        
        // Update day totals using calendar data
        if (currentData && currentData.calendar) {
            currentData.calendar.forEach(day => {
                updateDayTotal(day.day);
            });
        }
        
        console.log('All totals updated.');
    }

    // Show message function
    function showMessage(type, message) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        };
        
        const html = `
            <div class="alert ${alertClass[type]} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#message-container').html(html);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Submit attendance
    $('#submit-attendance-btn').on('click', function() {
        console.log('Submit attendance clicked');
        
        if (!currentData || !currentData.employees || currentData.employees.length === 0) {
            showMessage('error', 'No attendance data to submit. Please search for employees first.');
            return;
        }

        const formData = new FormData();
        
        // Add CSRF token
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Add site and month info
        formData.append('site_id', currentData.site.id);
        formData.append('month', $('#month').val());
        formData.append('user_type', $('#user_type').val());
        
        // Process attendance data into the format backend expects
        const processedAttendance = {};
        
        // Process shift checkboxes
        $('.shift-checkbox:checked').each(function() {
            const employeeId = $(this).data('employee-id');
            const date = $(this).data('date');
            const shift = $(this).data('shift');
            
            if (!processedAttendance[employeeId]) {
                processedAttendance[employeeId] = {};
            }
            if (!processedAttendance[employeeId][date]) {
                processedAttendance[employeeId][date] = {};
            }
            
            // Set the shift number (backend expects single shift field)
            processedAttendance[employeeId][date].shift = shift;
        });
        
        // Process OT checkboxes
        $('.ot-input:checked').each(function() {
            const employeeId = $(this).data('employee-id');
            const date = $(this).data('date');
            
            if (!processedAttendance[employeeId]) {
                processedAttendance[employeeId] = {};
            }
            if (!processedAttendance[employeeId][date]) {
                processedAttendance[employeeId][date] = {};
            }
            
            // Set OT flag (backend expects is_ot)
            processedAttendance[employeeId][date].is_ot = 1;
        });
        
        // Add processed attendance data using proper FormData array format
        Object.keys(processedAttendance).forEach(employeeId => {
            Object.keys(processedAttendance[employeeId]).forEach(date => {
                const attendance = processedAttendance[employeeId][date];
                if (attendance.shift) {
                    formData.append(`attendance[${employeeId}][${date}][shift]`, attendance.shift);
                }
                if (attendance.is_ot) {
                    formData.append(`attendance[${employeeId}][${date}][is_ot]`, attendance.is_ot);
                }
            });
        });
        
        console.log('Processed attendance data:', processedAttendance);

        // Submit via AJAX
        $.ajax({
            url: '{{ route("admin.bulk-attendance.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Submit success:', response);
                showMessage('success', response.message);
                
                // Optionally clear form or refresh
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            },
            error: function(xhr, status, error) {
                console.error('Submit error:', xhr.responseText);
                let errorMessage = 'Failed to submit attendance';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    console.error('Could not parse error response');
                }
                
                showMessage('error', errorMessage);
            }
        });
    });

    // Clear form
    $('#clear-btn').on('click', function() {
        // Reset all select fields to default
        $('#site_id').val('');
        $('#user_type').val('');
        $('#month_select').val('');
        $('#year_select').val('');
        $('#month').val('');
        
        // Uncheck all shift checkboxes
        $('input[name="shifts[]"]').prop('checked', false);
        
        // Hide attendance section
        $('#attendance-section').hide();
        $('#allocate-btn').prop('disabled', true);
        
        // Clear any messages
        $('#message-container').empty();
        
        // Show confirmation
        showMessage('info', 'Form cleared successfully. Please fill in the search criteria again.');
        
        console.log('Form cleared to default values');
    });

    // Remove shift functionality
    $('#remove-shift-btn').on('click', function() {
        if (confirm('Are you sure you want to clear all attendance data for this month? This action cannot be undone.')) {
            $('.shift-checkbox, .ot-input').prop('checked', false);
            updateAllTotals();
            showMessage('warning', 'All attendance data cleared for the current month.');
        }
    });

    // Submit/Approve/Lock handlers for master workflow
    $(document).on('click', '#btn-submit', function () {
        if (!window.masterRecord) return;
        const id = window.masterRecord.id;
        const self = $(this);
        self.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Submitting...');
        $.ajax({
            url: `/admin/bulk-attendance/${id}/submit`,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                showMessage('success', res.message || 'Submitted.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                showMessage('error', xhr.responseJSON?.message || 'Submit failed');
            },
            complete: function () { self.prop('disabled', false).html('<i class="la la-paper-plane"></i> Submit'); }
        });
    });

    $(document).on('click', '#btn-approve', function () {
        if (!window.masterRecord) return;
        const id = window.masterRecord.id;
        const self = $(this);
        self.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Approving...');
        $.ajax({
            url: `/admin/bulk-attendance/${id}/approve`,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                showMessage('success', res.message || 'Approved.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                showMessage('error', xhr.responseJSON?.message || 'Approve failed');
            },
            complete: function () { self.prop('disabled', false).html('<i class="la la-check"></i> Approve'); }
        });
    });

    $(document).on('click', '#btn-lock', function () {
        if (!window.masterRecord) return;
        const id = window.masterRecord.id;
        if (!confirm('Locking will prevent further edits. Continue?')) return;
        const self = $(this);
        self.prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Locking...');
        $.ajax({
            url: `/admin/bulk-attendance/${id}/lock`,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                showMessage('success', res.message || 'Locked.');
                setTimeout(() => location.reload(), 800);
            },
            error: function (xhr) {
                showMessage('error', xhr.responseJSON?.message || 'Lock failed');
            },
            complete: function () { self.prop('disabled', false).html('<i class="la la-lock"></i> Lock'); }
        });
    });

    // Handle Revert Changes button (Edit Mode)
    $(document).on('click', '#revert-changes-btn', function() {
        if (confirm('Are you sure you want to revert all changes? This will restore the previously saved attendance data.')) {
            // Reload the page to restore original state
            location.reload();
        }
    });

    // Handle Clear All Month button (Edit Mode)
    $(document).on('click', '#clear-all-month-btn', function() {
        if (confirm('Are you sure you want to clear ALL attendance for this month? This will remove all checkmarks.')) {
            // Clear all checkboxes
            $('.shift-checkbox, .ot-input').prop('checked', false);
            updateAllTotals();
            showMessage('warning', 'All attendance data cleared for this month.');
        }
    });

    // Handle Delete Record button (Edit Mode)
    $(document).on('click', '#delete-record-btn', function() {
        if (confirm('Are you sure you want to DELETE the entire attendance record for this site and month? This action cannot be undone!')) {
            const site_id = $('#site_id').val();
            const month = $('#month').val();
            const year = $('#year').val();
            
            if (!site_id || !month || !year) {
                showMessage('error', 'Please select site, month, and year first.');
                return;
            }
            
            // Show loading
            const originalText = $(this).html();
            $(this).prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Deleting...');
            
            // Make AJAX call to delete attendance record
            $.ajax({
                url: `/admin/bulk-attendance/delete`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    site_id: site_id,
                    month: month,
                    year: year
                },
                success: function(response) {
                    showMessage('success', 'Attendance record deleted successfully!');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'Unknown error occurred';
                    showMessage('error', 'Error deleting attendance record: ' + errorMsg);
                },
                complete: function() {
                    // Restore button
                    $('#delete-record-btn').prop('disabled', false).html(originalText);
                }
            });
        }
    });
});
</script>
@endpush