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

                    {{-- User Type Selection --}}
                    <div class="col-md-3">
                        <label for="user_type" class="form-label"><strong>To (User Type) *</strong></label>
                        <select id="user_type" name="user_type" class="form-control" required>
                            <option value="">Select User Type</option>
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
                        <button type="button" class="btn btn-info me-2" id="debug-btn">
                            <i class="la la-bug"></i> Debug
                        </button>
                        <button type="button" class="btn btn-success" id="allocate-btn" disabled>
                            <i class="la la-users"></i> Allocate
                        </button>
                    </div>
                </form>
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
                    <button type="button" class="btn btn-warning" id="remove-shift-btn">
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
                        <div class="btn-group flex-wrap" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="all-days">All Days</button>
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="except-sunday">Except Sunday</button>
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="except-saturday">Except Saturday</button>
                            <button type="button" class="btn btn-outline-primary btn-sm bulk-check" 
                                    data-action="except-weekend">Except Saturday & Sunday</button>
                            <button type="button" class="btn btn-outline-info btn-sm bulk-check" 
                                    data-action="shift-1">Check All 1st Shift</button>
                            <button type="button" class="btn btn-outline-info btn-sm bulk-check" 
                                    data-action="shift-2">Check All 2nd Shift</button>
                            <button type="button" class="btn btn-outline-info btn-sm bulk-check" 
                                    data-action="shift-3">Check All 3rd Shift</button>
                        </div>
                    </div>
                </div>

                {{-- Scrollable Table Container --}}
                <div class="table-responsive" style="max-height: 600px; overflow-x: auto;">
                    <table class="table table-bordered table-hover table-sm" id="attendance-table">
                        <thead class="table-dark sticky-top" id="table-head">
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
    .table-responsive {
        border: 1px solid #dee2e6;
    }
    
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .day-header {
        writing-mode: vertical-lr;
        text-orientation: mixed;
        min-width: 80px;
        max-width: 80px;
        text-align: center;
        font-size: 0.75rem;
        padding: 8px 4px;
    }
    
    .weekend-header {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .day-cell {
        min-width: 80px;
        max-width: 80px;
        text-align: center;
        vertical-align: middle;
        padding: 4px;
    }
    
    .weekend-cell {
        background-color: #f8f9fa;
    }
    
    .shift-checkbox {
        margin: 2px 0;
    }
    
    .ot-checkbox {
        margin: 2px 0;
    }
    
    .employee-row {
        height: 60px;
    }
    
    .total-cell {
        background-color: #e9ecef;
        font-weight: bold;
    }
    
    .btn-group .btn {
        margin: 2px;
    }

    #attendance-table th,
    #attendance-table td {
        white-space: nowrap;
    }

    .form-check-input {
        cursor: pointer;
    }

    .bulk-check {
        margin: 2px;
    }
</style>
@endpush

@push('after_scripts')
<script>
$(document).ready(function() {
    console.log('Bulk Attendance page loaded, jQuery ready');
    let currentData = {};
    let calendar = [];
    
    // Update hidden month field when month/year selectors change
    $('#month_select, #year_select').on('change', function() {
        const selectedMonth = $('#month_select').val();
        const selectedYear = $('#year_select').val();
        const monthValue = selectedYear + '-' + selectedMonth;
        $('#month').val(monthValue);
        console.log('Month/Year changed to:', monthValue);
    });

    // Search form submission
    $('#attendance-search-form').on('submit', function(e) {
        console.log('Form submission triggered!');
        e.preventDefault();
        
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

        console.log('Form data being sent:', formData); // Debug log

        if (!formData.site_id || !formData.user_type || !formData.month) {
            console.log('Validation failed: missing required fields');
            showMessage('error', 'Please fill all required fields.');
            return;
        }

        if (formData.shifts.length === 0) {
            console.log('Validation failed: no shifts selected');
            showMessage('error', 'Please select at least one shift.');
            return;
        }

        console.log('Calling searchEmployees with:', formData);
        searchEmployees(formData);
    });

    // Debug button for testing
    $('#debug-btn').on('click', function() {
        console.log('=== DEBUG INFO ===');
        console.log('Site ID value:', $('#site_id').val());
        console.log('User Type value:', $('#user_type').val());
        console.log('Month value:', $('#month').val());
        console.log('Selected shifts:', $('input[name="shifts[]"]:checked').map(function() {
            return this.value;
        }).get());
        console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));
        
        // Test if form elements exist
        console.log('Form exists:', $('#attendance-search-form').length);
        console.log('Site select options count:', $('#site_id option').length);
        console.log('User type select options count:', $('#user_type option').length);
        
        // Show available options
        console.log('Available sites:');
        $('#site_id option').each(function() {
            if ($(this).val()) {
                console.log('  -', $(this).val(), ':', $(this).text());
            }
        });
        
        console.log('Available user types:');
        $('#user_type option').each(function() {
            if ($(this).val()) {
                console.log('  -', $(this).val(), ':', $(this).text());
            }
        });
    });

    // Search employees function
    function searchEmployees(formData) {
        console.log('searchEmployees called with data:', formData);
        $('#loading-spinner').show();
        $('#attendance-section').hide();

        $.ajax({
            url: '{{ route("admin.bulk-attendance.search") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                console.log('AJAX request starting...');
            },
            success: function(response) {
                console.log('AJAX success, response received:', response);
                $('#loading-spinner').hide();
                
                if (response.success) {
                    currentData = response.data;
                    calendar = response.data.calendar;
                    console.log('Search Results:', {
                        employees: response.data.employees.length,
                        calendar_days: response.data.calendar.length,
                        site: response.data.site.name,
                        user_type: response.data.user_type,
                        debug_info: response.data.debug_info
                    });
                    
                    if (response.data.employees.length === 0) {
                        showMessage('warning', `No employees found for position "${response.data.user_type}" at site "${response.data.site.name}". Available positions for this site: ${response.data.debug_info.all_positions_for_client.join(', ') || 'None'}`);
                        $('#attendance-section').hide();
                        $('#allocate-btn').prop('disabled', true);
                        return;
                    }
                    
                    buildAttendanceTable(response.data);
                    $('#attendance-section').show();
                    $('#allocate-btn').prop('disabled', false);
                } else {
                    console.log('Search failed with errors:', response.errors);
                    showMessage('error', 'Error loading data: ' + JSON.stringify(response.errors));
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error occurred:');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                $('#loading-spinner').hide();
                let errorMsg = 'Error loading data.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                }
                showMessage('error', errorMsg);
            }
        });
    }

    // Build attendance table
    function buildAttendanceTable(data) {
        const { employees, calendar, site, existing_attendance } = data;
        
        console.log('=== BUILD TABLE DEBUG ===');
        console.log('Employees received:', employees);
        console.log('Calendar received:', calendar);
        console.log('Site received:', site);
        console.log('Existing attendance:', existing_attendance);
        
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
        
        // Show edit mode message if there's existing data
        if (hasExistingData) {
            $('#attendance-info').html(`
                <div class="alert alert-info mb-3">
                    <i class="la la-edit"></i> <strong>Edit Mode:</strong> 
                    Attendance data already exists for <strong>${site.name}</strong> in the selected month. 
                    You are now editing existing records. Changes will overwrite the current data.
                </div>
            `);
            $('#submit-attendance-btn').html('<i class="la la-save"></i> Update Attendance');
        } else {
            $('#attendance-info').html(`
                <div class="alert alert-success mb-3">
                    <i class="la la-plus-circle"></i> <strong>Create Mode:</strong> 
                    Creating new attendance records for <strong>${site.name}</strong> in the selected month.
                </div>
            `);
            $('#submit-attendance-btn').html('<i class="la la-save"></i> Submit Full Month Attendance');
        }
        
        if (employees.length === 0) {
            console.log('No employees, showing empty message');
            $('#attendance-tbody').html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="la la-exclamation-triangle" style="font-size: 2rem; color: #ffc107;"></i>
                        <h4>No employees found</h4>
                        <p>No employees found for the selected position. Try a different user type.</p>
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
                <th>Sr. No</th>
                <th>Site Name</th>
                <th>Employee</th>
                <th>User Type</th>
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

        // Build body
        let bodyHtml = '';
        employees.forEach((employee, index) => {
            bodyHtml += `
                <tr class="employee-row" data-employee-id="${employee.id}">
                    <td>${index + 1}</td>
                    <td>${site.name}</td>
                    <td>
                        <strong>${employee.first_name} ${employee.last_name}</strong>
                        ${employee.job_role ? `<br><small class="text-muted">${employee.job_role}</small>` : ''}
                    </td>
                    <td>
                        <span class="badge bg-secondary">${employee.position}</span>
                    </td>
            `;

            calendar.forEach(day => {
                const isWeekend = day.is_weekend;
                const weekendClass = isWeekend ? 'weekend-cell' : '';
                
                // Check for existing attendance
                const existingDay = existing_attendance[employee.id] && existing_attendance[employee.id][day.date];
                const checkedShift = existingDay ? String(existingDay.shift) : '';
                const checkedOT = existingDay ? Boolean(existingDay.is_ot) : false;
                const existingClass = existingDay ? 'existing-data' : '';

                bodyHtml += `
                    <td class="day-cell ${weekendClass} ${existingClass}" data-date="${day.date}" data-day="${day.day}">`;
                        <div>
                            <select name="attendance[${employee.id}][${day.date}][shift]" 
                                    class="form-select form-select-sm shift-checkbox" 
                                    data-employee-id="${employee.id}" 
                                    data-date="${day.date}">
                                <option value="">-</option>
                                <option value="1" ${checkedShift === '1' ? 'selected' : ''}>S1</option>
                                <option value="2" ${checkedShift === '2' ? 'selected' : ''}>S2</option>
                                <option value="3" ${checkedShift === '3' ? 'selected' : ''}>S3</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" 
                                   name="attendance[${employee.id}][${day.date}][is_ot]" 
                                   value="1" 
                                   class="form-check-input ot-checkbox" 
                                   data-employee-id="${employee.id}" 
                                   data-date="${day.date}"
                                   ${checkedOT ? 'checked' : ''}>
                            <label class="form-check-label small">OT</label>
                        </div>
                    </td>
                `;
            });

            bodyHtml += `
                    <td class="total-cell">
                        <span class="employee-total" data-employee-id="${employee.id}">0</span>
                    </td>
                </tr>
            `;
        });
        $('#attendance-tbody').html(bodyHtml);

        // Update totals
        updateAllTotals();

        // Bind events
        bindAttendanceEvents();
    }

    // Bind attendance events
    function bindAttendanceEvents() {
        // Shift change event
        $('.shift-checkbox').on('change', function() {
            updateTotals();
        });

        // Bulk check events
        $('.bulk-check').on('click', function() {
            const action = $(this).data('action');
            applyBulkAction(action);
        });

        // Remove shift button
        $('#remove-shift-btn').on('click', function() {
            if (confirm('Are you sure you want to remove all attendance data?')) {
                $('.shift-checkbox').val('');
                $('.ot-checkbox').prop('checked', false);
                updateTotals();
            }
        });

        // Submit attendance
        $('#submit-attendance-btn').on('click', function() {
            submitAttendance();
        });
    }

    // Apply bulk actions
    function applyBulkAction(action) {
        $('.shift-checkbox').each(function() {
            const $this = $(this);
            const date = $this.data('date');
            const dayElement = $this.closest('td');
            const isWeekend = dayElement.hasClass('weekend-cell');
            
            let shouldCheck = false;
            let shiftValue = '1'; // Default shift

            switch(action) {
                case 'all-days':
                    shouldCheck = true;
                    break;
                case 'except-sunday':
                    shouldCheck = !calendar.find(d => d.date === date)?.is_sunday;
                    break;
                case 'except-saturday':
                    shouldCheck = !calendar.find(d => d.date === date)?.is_saturday;
                    break;
                case 'except-weekend':
                    shouldCheck = !isWeekend;
                    break;
                case 'shift-1':
                    shouldCheck = true;
                    shiftValue = '1';
                    break;
                case 'shift-2':
                    shouldCheck = true;
                    shiftValue = '2';
                    break;
                case 'shift-3':
                    shouldCheck = true;
                    shiftValue = '3';
                    break;
            }

            if (shouldCheck) {
                $this.val(shiftValue);
            }
        });

        updateTotals();
    }

    // Update totals
    function updateTotals() {
        updateAllTotals();
    }

    function updateAllTotals() {
        // Update employee totals
        $('[data-employee-id]').each(function() {
            const employeeId = $(this).data('employee-id');
            if ($(this).hasClass('employee-total')) {
                const total = $(`.shift-checkbox[data-employee-id="${employeeId}"]`).filter(function() {
                    return $(this).val() !== '';
                }).length;
                $(this).text(total);
            }
        });

        // Update day totals
        calendar.forEach(day => {
            const total = $(`.shift-checkbox[data-date="${day.date}"]`).filter(function() {
                return $(this).val() !== '';
            }).length;
            $(`#day-total-${day.day}`).text(total);
        });
    }

    // Submit attendance
    function submitAttendance() {
        const attendanceData = {};
        
        // Validate required fields first
        const siteId = $('#site_id').val();
        const userType = $('#user_type').val();
        const month = $('#month').val();
        
        if (!siteId || !userType || !month) {
            showMessage('error', 'Please select Site, User Type, and Month before submitting attendance.');
            return;
        }
        
        $('.shift-checkbox').each(function() {
            const employeeId = $(this).data('employee-id');
            const date = $(this).data('date');
            const shift = $(this).val();
            
            if (shift) {
                if (!attendanceData[employeeId]) {
                    attendanceData[employeeId] = {};
                }
                
                const isOT = $(`.ot-checkbox[data-employee-id="${employeeId}"][data-date="${date}"]`).is(':checked');
                
                attendanceData[employeeId][date] = {
                    shift: shift,
                    is_ot: isOT ? 1 : 0  // Send as 1/0 instead of boolean
                };
            }
        });

        console.log('Attendance Data:', attendanceData); // Debug log

        if (Object.keys(attendanceData).length === 0) {
            showMessage('warning', 'No attendance data to submit. Please mark attendance for at least one employee.');
            return;
        }

        if (!confirm('Are you sure you want to submit the attendance data?')) {
            return;
        }

        const submitData = {
            site_id: siteId,
            user_type: userType,
            month: month,
            attendance: attendanceData,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log('Submit Data:', submitData); // Debug log

        $('#submit-attendance-btn').prop('disabled', true).html('<i class="la la-spinner la-spin"></i> Submitting...');

        $.ajax({
            url: '{{ route("admin.bulk-attendance.store") }}',
            method: 'POST',
            data: submitData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                const buttonText = window.attendanceMode === 'edit' ? '<i class="la la-save"></i> Update Attendance' : '<i class="la la-save"></i> Submit Full Month Attendance';
                $('#submit-attendance-btn').prop('disabled', false).html(buttonText);
                
                console.log('Success Response:', response); // Debug log
                
                if (response.success) {
                    showMessage('success', response.message);
                } else {
                    showMessage('error', response.message || 'Error submitting attendance.');
                }
            },
            error: function(xhr) {
                const buttonText = window.attendanceMode === 'edit' ? '<i class="la la-save"></i> Update Attendance' : '<i class="la la-save"></i> Submit Full Month Attendance';
                $('#submit-attendance-btn').prop('disabled', false).html(buttonText);
                
                console.log('Error Response:', xhr); // Debug log
                
                let errorMsg = 'Error submitting attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(', ');
                } else if (xhr.responseText) {
                    errorMsg += ' Details: ' + xhr.responseText.substring(0, 200);
                }
                showMessage('error', errorMsg);
            }
        });
    }

    // Show message function
    function showMessage(type, message) {
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'success' ? 'alert-success' : 'alert-info';
        
        const html = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#message-container').html(html);
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush