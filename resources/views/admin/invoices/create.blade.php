@extends(backpack_view('blank'))

@push('before_styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endpush

@section('title', 'Create Invoice')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Create Invoice</h3>
        <div class="card-tools">
            <a href="{{ route('client-invoice.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    
    <form id="invoice-form" method="POST" action="{{ route('client-invoice.store') }}">
        @csrf
        
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Basic Invoice Information -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client_id">Client *</label>
                        <select name="client_id" id="client_id" class="form-control" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="month">Month *</label>
                        <input type="text" name="month" id="month" class="form-control datepicker" 
                               placeholder="Select Month and Year" required readonly>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="bill_date">Bill Date *</label>
                        <input type="date" name="bill_date" id="bill_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <!-- Employee Attendance Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Employee Attendance Summary</h5>
                    <button type="button" id="fetch-attendance" class="btn btn-info mb-3">
                        <i class="fa fa-refresh"></i> Fetch Attendance Data
                    </button>
                    
                    <div id="employee-table-container" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="employee-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Duty Days</th>
                                        <th>OT Hours</th>
                                        <th>Daily Rate</th>
                                        <th>OT Rate/Hr</th>
                                        <th>Payment</th>
                                        <th>OT Payment</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-tbody">
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td><strong>Total</strong></td>
                                        <td id="total-duty-days">0.00</td>
                                        <td id="total-ot-hours">0.00</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td id="total-payment">₹0.00</td>
                                        <td id="total-ot-payment">₹0.00</td>
                                        <td id="total-all-payment"><strong>₹0.00</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Charges Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Additional Charges (Debit Notes)</h5>
                    <button type="button" id="add-charge" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Add Charge
                    </button>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="charges-table">
                            <thead class="bg-light">
                                <tr>
                                    <th width="20%">Date</th>
                                    <th width="20%">Amount</th>
                                    <th width="50%">Comment</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="charges-tbody">
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td><strong>Total</strong></td>
                                    <td id="total-charges"><strong>₹0.00</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tax Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Tax Details</h5>
                    <button type="button" id="add-tax" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Add Tax
                    </button>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tax-table">
                            <thead class="bg-light">
                                <tr>
                                    <th width="20%">Tax Type</th>
                                    <th width="15%">Tax %</th>
                                    <th width="20%">Tax Amount</th>
                                    <th width="25%">Tax No</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tax-tbody">
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td><strong>Total Tax</strong></td>
                                    <td>-</td>
                                    <td id="total-tax"><strong>₹0.00</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Service Tax Details Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Service Tax Details</h5>
                    <button type="button" id="add-service-tax" class="btn btn-success btn-sm mb-3">
                        <i class="fa fa-plus"></i> Add Service Tax
                    </button>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="service-tax-table">
                            <thead class="bg-light">
                                <tr>
                                    <th width="15%">Amount</th>
                                    <th width="15%">Service Type</th>
                                    <th width="15%">Tax Type</th>
                                    <th width="10%">Tax %</th>
                                    <th width="15%">Final Amount</th>
                                    <th width="20%">Comment</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="service-tax-tbody">
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td><strong>Total</strong></td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td id="total-service-tax"><strong>₹0.00</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Invoice Calculations -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_charge_percent">Service Charge %</label>
                        <input type="number" name="service_charge_percent" id="service_charge_percent" 
                               class="form-control calculation-input" step="0.01" min="0" max="100">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_charge_amount">Service Charge Amount</label>
                        <input type="number" name="service_charge_amount" id="service_charge_amount" 
                               class="form-control calculation-input" step="0.01" min="0" readonly>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discount_percent">Discount %</label>
                        <input type="number" name="discount_percent" id="discount_percent" 
                               class="form-control calculation-input" step="0.01" min="0" max="100">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discount_amount">Discount Amount</label>
                        <input type="number" name="discount_amount" id="discount_amount" 
                               class="form-control calculation-input" step="0.01" min="0" readonly>
                    </div>
                </div>
            </div>

            <!-- Total Summary -->
            <div class="row mt-4">
                <div class="col-md-6 offset-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td>Invoice Amount:</td>
                                    <td class="text-right" id="display-invoice-amount">₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Service Charge:</td>
                                    <td class="text-right" id="display-service-charge">₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Additional Charges:</td>
                                    <td class="text-right" id="display-additional-charges">₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Taxes:</td>
                                    <td class="text-right" id="display-taxes">₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Service Tax:</td>
                                    <td class="text-right" id="display-service-tax">₹0.00</td>
                                </tr>
                                <tr>
                                    <td>Discount:</td>
                                    <td class="text-right" id="display-discount">-₹0.00</td>
                                </tr>
                                <tr class="font-weight-bold bg-primary text-white">
                                    <td><strong>Grand Total:</strong></td>
                                    <td class="text-right" id="display-grand-total"><strong>₹0.00</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="comments">Comments</label>
                        <textarea name="comments" id="comments" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="monthly_comment">Monthly Comment</label>
                        <textarea name="monthly_comment" id="monthly_comment" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- Hidden calculation fields -->
            <input type="hidden" name="invoice_amount" id="invoice_amount">
            <input type="hidden" name="gross_bill_amount" id="gross_bill_amount">
            <input type="hidden" name="grand_total" id="grand_total">
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    <label class="form-check-label">
                        <input type="checkbox" name="send_mail" id="send_mail" class="form-check-input">
                        Send Invoice via Email
                    </label>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('after_scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
$(document).ready(function() {
    $('.datepicker').datepicker({
        format: 'MM yyyy',
        minViewMode: 'months',
        autoclose: true,
        todayHighlight: true,
        startView: 'months'
    });
});
</script>
<script>
let employeeIndex = 0;
let chargeIndex = 0;
let taxIndex = 0;
let serviceTaxIndex = 0;

$(document).ready(function() {
    // Fetch attendance data
    $('#fetch-attendance').click(function() {
        const clientId = $('#client_id').val();
        const month = $('#month').val();
        
        if (!clientId || !month) {
            alert('Please select client and enter month first');
            return;
        }
        
        // Convert month from "MM yyyy" to "yyyy-mm" format
        const monthDate = new Date(month + ' 1');
        const formattedMonth = monthDate.getFullYear() + '-' + 
                              String(monthDate.getMonth() + 1).padStart(2, '0');
        
        $.ajax({
            url: '{{ url("admin/client-invoice/get-attendance") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                client_id: clientId,
                month: formattedMonth
            },
            success: function(response) {
                if (response.success) {
                    populateEmployeeTable(response.employees);
                    $('#employee-table-container').show();
                    
                    // Update totals from backend response
                    $('#total-duty-days').text(response.total_duty_days.toFixed(2));
                    $('#total-ot-hours').text(response.total_ot_hours.toFixed(2));
                    $('#total-all-payment').text('₹' + response.total.toFixed(2));
                    
                    calculateTotals();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to fetch attendance data');
            }
        });
    });
    
    // Add charge row
    $('#add-charge').click(function() {
        addChargeRow();
    });
    
    // Add tax row
    $('#add-tax').click(function() {
        addTaxRow();
    });
    
    // Add service tax row
    $('#add-service-tax').click(function() {
        addServiceTaxRow();
    });
    
    // Calculation inputs
    $('.calculation-input').on('input', function() {
        calculateTotals();
    });
    
    // Form submission
    $('#invoice-form').submit(function(e) {
        // Update hidden fields before submission
        updateHiddenFields();
    });
});

function populateEmployeeTable(employees) {
    const tbody = $('#employee-tbody');
    tbody.empty();
    
    employees.forEach(function(emp, index) {
        const row = `
            <tr>
                <td>
                    ${emp.employee_name}
                    <input type="hidden" name="employees[${index}][employee_id]" value="${emp.employee_id}">
                </td>
                <td>
                    <input type="number" name="employees[${index}][duty_days]" 
                           class="form-control form-control-sm emp-input" 
                           value="${emp.duty_days}" step="0.01">
                </td>
                <td>
                    <input type="number" name="employees[${index}][overtime_hours]" 
                           class="form-control form-control-sm emp-input" 
                           value="${emp.overtime_hours}" step="0.01">
                </td>
                <td>
                    <input type="number" name="employees[${index}][daily_rate]" 
                           class="form-control form-control-sm emp-input" 
                           value="${emp.daily_rate}" step="0.01">
                </td>
                <td>
                    <input type="number" name="employees[${index}][overtime_rate]" 
                           class="form-control form-control-sm emp-input" 
                           value="${emp.overtime_rate}" step="0.01">
                </td>
                <td>
                    <input type="number" name="employees[${index}][payment]" 
                           class="form-control form-control-sm emp-payment" 
                           value="${emp.payment}" step="0.01" readonly>
                </td>
                <td>
                    <input type="number" name="employees[${index}][overtime_payment]" 
                           class="form-control form-control-sm emp-ot-payment" 
                           value="${emp.overtime_payment}" step="0.01" readonly>
                </td>
                <td>
                    <input type="number" name="employees[${index}][total_payment]" 
                           class="form-control form-control-sm emp-total" 
                           value="${emp.total_payment}" step="0.01" readonly>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Bind calculation events
    $('.emp-input').on('input', function() {
        calculateEmployeeRow($(this).closest('tr'));
        calculateTotals();
    });
}

function calculateEmployeeRow(row) {
    const dutyDays = parseFloat(row.find('input[name*="[duty_days]"]').val()) || 0;
    const otHours = parseFloat(row.find('input[name*="[overtime_hours]"]').val()) || 0;
    const dailyRate = parseFloat(row.find('input[name*="[daily_rate]"]').val()) || 0;
    const otRate = parseFloat(row.find('input[name*="[overtime_rate]"]').val()) || 0;
    
    const payment = dutyDays * dailyRate;
    const otPayment = otHours * otRate;
    const total = payment + otPayment;
    
    row.find('.emp-payment').val(payment.toFixed(2));
    row.find('.emp-ot-payment').val(otPayment.toFixed(2));
    row.find('.emp-total').val(total.toFixed(2));
}

function addChargeRow() {
    const row = `
        <tr>
            <td>
                <input type="date" name="additional_charges[${chargeIndex}][date]" 
                       class="form-control form-control-sm" value="${new Date().toISOString().split('T')[0]}">
            </td>
            <td>
                <input type="number" name="additional_charges[${chargeIndex}][amount]" 
                       class="form-control form-control-sm charge-amount" step="0.01">
            </td>
            <td>
                <input type="text" name="additional_charges[${chargeIndex}][comment]" 
                       class="form-control form-control-sm">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#charges-tbody').append(row);
    chargeIndex++;
    
    // Bind events
    $('.charge-amount').off('input').on('input', calculateTotals);
    $('.remove-row').off('click').on('click', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });
}

function addTaxRow() {
    const row = `
        <tr>
            <td>
                <select name="taxes[${taxIndex}][tax_type]" class="form-control form-control-sm">
                    <option value="SGST">SGST</option>
                    <option value="CGST">CGST</option>
                    <option value="IGST">IGST</option>
                    <option value="VAT">VAT</option>
                    <option value="CST">CST</option>
                </select>
            </td>
            <td>
                <input type="number" name="taxes[${taxIndex}][tax_percent]" 
                       class="form-control form-control-sm tax-percent" step="0.01">
            </td>
            <td>
                <input type="number" name="taxes[${taxIndex}][tax_amount]" 
                       class="form-control form-control-sm tax-amount" step="0.01" readonly>
            </td>
            <td>
                <input type="text" name="taxes[${taxIndex}][tax_no]" 
                       class="form-control form-control-sm">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#tax-tbody').append(row);
    taxIndex++;
    
    // Bind events
    $('.tax-percent').off('input').on('input', function() {
        calculateTaxRow($(this).closest('tr'));
        calculateTotals();
    });
    $('.remove-row').off('click').on('click', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });
}

function addServiceTaxRow() {
    const row = `
        <tr>
            <td>
                <input type="number" name="service_tax_details[${serviceTaxIndex}][amount]" 
                       class="form-control form-control-sm service-tax-amount" step="0.01">
            </td>
            <td>
                <input type="text" name="service_tax_details[${serviceTaxIndex}][service_type]" 
                       class="form-control form-control-sm">
            </td>
            <td>
                <input type="text" name="service_tax_details[${serviceTaxIndex}][tax_type]" 
                       class="form-control form-control-sm">
            </td>
            <td>
                <input type="number" name="service_tax_details[${serviceTaxIndex}][tax_percent]" 
                       class="form-control form-control-sm service-tax-percent" step="0.01">
            </td>
            <td>
                <input type="number" name="service_tax_details[${serviceTaxIndex}][final_amount]" 
                       class="form-control form-control-sm service-tax-final" step="0.01" readonly>
            </td>
            <td>
                <input type="text" name="service_tax_details[${serviceTaxIndex}][comment]" 
                       class="form-control form-control-sm">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#service-tax-tbody').append(row);
    serviceTaxIndex++;
    
    // Bind events
    $('.service-tax-amount, .service-tax-percent').off('input').on('input', function() {
        calculateServiceTaxRow($(this).closest('tr'));
        calculateTotals();
    });
    $('.remove-row').off('click').on('click', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });
}

function calculateTaxRow(row) {
    const percent = parseFloat(row.find('.tax-percent').val()) || 0;
    const invoiceAmount = getInvoiceBaseAmount();
    const taxAmount = (invoiceAmount * percent) / 100;
    row.find('.tax-amount').val(taxAmount.toFixed(2));
}

function calculateServiceTaxRow(row) {
    const amount = parseFloat(row.find('.service-tax-amount').val()) || 0;
    const percent = parseFloat(row.find('.service-tax-percent').val()) || 0;
    const finalAmount = amount + (amount * percent / 100);
    row.find('.service-tax-final').val(finalAmount.toFixed(2));
}

function getInvoiceBaseAmount() {
    let total = 0;
    $('.emp-total').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    return total;
}

function calculateTotals() {
    const invoiceAmount = getInvoiceBaseAmount();
    
    // Additional charges
    let additionalCharges = 0;
    $('.charge-amount').each(function() {
        additionalCharges += parseFloat($(this).val()) || 0;
    });
    
    // Service charge
    const serviceChargePercent = parseFloat($('#service_charge_percent').val()) || 0;
    const serviceChargeAmount = (invoiceAmount * serviceChargePercent) / 100;
    $('#service_charge_amount').val(serviceChargeAmount.toFixed(2));
    
    // Taxes
    let totalTax = 0;
    $('.tax-amount').each(function() {
        totalTax += parseFloat($(this).val()) || 0;
    });
    
    // Service tax
    let totalServiceTax = 0;
    $('.service-tax-final').each(function() {
        totalServiceTax += parseFloat($(this).val()) || 0;
    });
    
    // Discount
    const discountPercent = parseFloat($('#discount_percent').val()) || 0;
    const discountAmount = ((invoiceAmount + serviceChargeAmount + additionalCharges) * discountPercent) / 100;
    $('#discount_amount').val(discountAmount.toFixed(2));
    
    // Grand total
    const grossBillAmount = invoiceAmount + serviceChargeAmount + additionalCharges + totalTax + totalServiceTax;
    const grandTotal = grossBillAmount - discountAmount;
    
    // Update displays
    $('#display-invoice-amount').text('₹' + invoiceAmount.toFixed(2));
    $('#display-service-charge').text('₹' + serviceChargeAmount.toFixed(2));
    $('#display-additional-charges').text('₹' + additionalCharges.toFixed(2));
    $('#display-taxes').text('₹' + totalTax.toFixed(2));
    $('#display-service-tax').text('₹' + totalServiceTax.toFixed(2));
    $('#display-discount').text('-₹' + discountAmount.toFixed(2));
    $('#display-grand-total').text('₹' + grandTotal.toFixed(2));
    
    // Update totals in tables
    $('#total-charges').text('₹' + additionalCharges.toFixed(2));
    $('#total-tax').text('₹' + totalTax.toFixed(2));
    $('#total-service-tax').text('₹' + totalServiceTax.toFixed(2));
    
    // Employee table totals
    updateEmployeeTotals();
}

function updateEmployeeTotals() {
    let totalPayment = 0;
    let totalOTPayment = 0;
    
    $('#employee-tbody tr').each(function() {
        totalPayment += parseFloat($(this).find('.emp-payment').val()) || 0;
        totalOTPayment += parseFloat($(this).find('.emp-ot-payment').val()) || 0;
    });
    
    $('#total-payment').text('₹' + totalPayment.toFixed(2));
    $('#total-ot-payment').text('₹' + totalOTPayment.toFixed(2));
}

function updateHiddenFields() {
    const invoiceAmount = getInvoiceBaseAmount();
    let additionalCharges = 0;
    $('.charge-amount').each(function() {
        additionalCharges += parseFloat($(this).val()) || 0;
    });
    
    const serviceChargeAmount = parseFloat($('#service_charge_amount').val()) || 0;
    const discountAmount = parseFloat($('#discount_amount').val()) || 0;
    
    let totalTax = 0;
    $('.tax-amount').each(function() {
        totalTax += parseFloat($(this).val()) || 0;
    });
    
    let totalServiceTax = 0;
    $('.service-tax-final').each(function() {
        totalServiceTax += parseFloat($(this).val()) || 0;
    });
    
    const grossBillAmount = invoiceAmount + serviceChargeAmount + additionalCharges + totalTax + totalServiceTax;
    const grandTotal = grossBillAmount - discountAmount;
    
    $('#invoice_amount').val(invoiceAmount.toFixed(2));
    $('#gross_bill_amount').val(grossBillAmount.toFixed(2));
    $('#grand_total').val(grandTotal.toFixed(2));
}
</script>
@endpush

@endsection