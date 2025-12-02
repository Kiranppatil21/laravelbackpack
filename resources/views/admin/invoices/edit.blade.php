@extends(backpack_view('blank'))

@section('title', 'Edit Invoice')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Invoice: {{ $invoice->invoice_no }}</h3>
        <div class="card-tools">
            <a href="{{ route('client-invoice.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('admin.client-invoice.pdf', $invoice->id) }}" class="btn btn-info" target="_blank">
                <i class="fa fa-file-pdf"></i> View PDF
            </a>
        </div>
    </div>
    
    <form id="invoice-form" method="POST" action="{{ route('client-invoice.update', $invoice->id) }}">
        @csrf
        @method('PUT')
        
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
                                <option value="{{ $client->id }}" {{ $client->id == $invoice->client_id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="month">Month *</label>
                        <input type="text" name="month" id="month" class="form-control" 
                               value="{{ $invoice->month }}" placeholder="e.g., January 2024" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="bill_date">Bill Date *</label>
                        <input type="date" name="bill_date" id="bill_date" class="form-control" 
                               value="{{ $invoice->bill_date }}" required>
                    </div>
                </div>
            </div>

            <!-- Employee Attendance Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Employee Attendance Summary</h5>
                    <button type="button" id="fetch-attendance" class="btn btn-info mb-3">
                        <i class="fa fa-refresh"></i> Refresh Attendance Data
                    </button>
                    
                    <div id="employee-table-container">
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
                                    @foreach($invoice->invoiceEmployees as $index => $empInvoice)
                                    <tr>
                                        <td>
                                            {{ $empInvoice->employee->first_name }} {{ $empInvoice->employee->last_name }}
                                            <input type="hidden" name="employees[{{ $index }}][employee_id]" value="{{ $empInvoice->employee_id }}">
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][duty_days]" 
                                                   class="form-control form-control-sm emp-input" 
                                                   value="{{ $empInvoice->duty_days }}" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][overtime_hours]" 
                                                   class="form-control form-control-sm emp-input" 
                                                   value="{{ $empInvoice->overtime_hours }}" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][daily_rate]" 
                                                   class="form-control form-control-sm emp-input" 
                                                   value="{{ $empInvoice->daily_rate }}" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][overtime_rate]" 
                                                   class="form-control form-control-sm emp-input" 
                                                   value="{{ $empInvoice->overtime_rate }}" step="0.01">
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][payment]" 
                                                   class="form-control form-control-sm emp-payment" 
                                                   value="{{ $empInvoice->payment }}" step="0.01" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][overtime_payment]" 
                                                   class="form-control form-control-sm emp-ot-payment" 
                                                   value="{{ $empInvoice->overtime_payment }}" step="0.01" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="employees[{{ $index }}][total_payment]" 
                                                   class="form-control form-control-sm emp-total" 
                                                   value="{{ $empInvoice->total_payment }}" step="0.01" readonly>
                                        </td>
                                    </tr>
                                    @endforeach
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
                                @foreach($invoice->additionalCharges as $index => $charge)
                                <tr>
                                    <td>
                                        <input type="date" name="additional_charges[{{ $index }}][date]" 
                                               class="form-control form-control-sm" value="{{ $charge->date }}">
                                    </td>
                                    <td>
                                        <input type="number" name="additional_charges[{{ $index }}][amount]" 
                                               class="form-control form-control-sm charge-amount" 
                                               value="{{ $charge->amount }}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="text" name="additional_charges[{{ $index }}][comment]" 
                                               class="form-control form-control-sm" value="{{ $charge->comment }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
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
                                @foreach($invoice->taxes as $index => $tax)
                                <tr>
                                    <td>
                                        <select name="taxes[{{ $index }}][tax_type]" class="form-control form-control-sm">
                                            <option value="SGST" {{ $tax->tax_type == 'SGST' ? 'selected' : '' }}>SGST</option>
                                            <option value="CGST" {{ $tax->tax_type == 'CGST' ? 'selected' : '' }}>CGST</option>
                                            <option value="IGST" {{ $tax->tax_type == 'IGST' ? 'selected' : '' }}>IGST</option>
                                            <option value="VAT" {{ $tax->tax_type == 'VAT' ? 'selected' : '' }}>VAT</option>
                                            <option value="CST" {{ $tax->tax_type == 'CST' ? 'selected' : '' }}>CST</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="taxes[{{ $index }}][tax_percent]" 
                                               class="form-control form-control-sm tax-percent" 
                                               value="{{ $tax->tax_percent }}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="taxes[{{ $index }}][tax_amount]" 
                                               class="form-control form-control-sm tax-amount" 
                                               value="{{ $tax->tax_amount }}" step="0.01" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="taxes[{{ $index }}][tax_no]" 
                                               class="form-control form-control-sm" value="{{ $tax->tax_no }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
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
                                @foreach($invoice->serviceTaxDetails as $index => $serviceTax)
                                <tr>
                                    <td>
                                        <input type="number" name="service_tax_details[{{ $index }}][amount]" 
                                               class="form-control form-control-sm service-tax-amount" 
                                               value="{{ $serviceTax->amount }}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="text" name="service_tax_details[{{ $index }}][service_type]" 
                                               class="form-control form-control-sm" value="{{ $serviceTax->service_type }}">
                                    </td>
                                    <td>
                                        <input type="text" name="service_tax_details[{{ $index }}][tax_type]" 
                                               class="form-control form-control-sm" value="{{ $serviceTax->tax_type }}">
                                    </td>
                                    <td>
                                        <input type="number" name="service_tax_details[{{ $index }}][tax_percent]" 
                                               class="form-control form-control-sm service-tax-percent" 
                                               value="{{ $serviceTax->tax_percent }}" step="0.01">
                                    </td>
                                    <td>
                                        <input type="number" name="service_tax_details[{{ $index }}][final_amount]" 
                                               class="form-control form-control-sm service-tax-final" 
                                               value="{{ $serviceTax->final_amount }}" step="0.01" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="service_tax_details[{{ $index }}][comment]" 
                                               class="form-control form-control-sm" value="{{ $serviceTax->comment }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
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
                               class="form-control calculation-input" step="0.01" min="0" max="100"
                               value="{{ $invoice->service_charge_percent }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="service_charge_amount">Service Charge Amount</label>
                        <input type="number" name="service_charge_amount" id="service_charge_amount" 
                               class="form-control calculation-input" step="0.01" min="0" readonly
                               value="{{ $invoice->service_charge_amount }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discount_percent">Discount %</label>
                        <input type="number" name="discount_percent" id="discount_percent" 
                               class="form-control calculation-input" step="0.01" min="0" max="100"
                               value="{{ $invoice->discount_percent }}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discount_amount">Discount Amount</label>
                        <input type="number" name="discount_amount" id="discount_amount" 
                               class="form-control calculation-input" step="0.01" min="0" readonly
                               value="{{ $invoice->discount_amount }}">
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
                                    <td class="text-right" id="display-invoice-amount">₹{{ number_format($invoice->invoice_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Service Charge:</td>
                                    <td class="text-right" id="display-service-charge">₹{{ number_format($invoice->service_charge_amount, 2) }}</td>
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
                                    <td class="text-right" id="display-discount">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
                                </tr>
                                <tr class="font-weight-bold bg-primary text-white">
                                    <td><strong>Grand Total:</strong></td>
                                    <td class="text-right" id="display-grand-total"><strong>₹{{ number_format($invoice->grand_total, 2) }}</strong></td>
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
                        <textarea name="comments" id="comments" class="form-control" rows="3">{{ $invoice->comments }}</textarea>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="monthly_comment">Monthly Comment</label>
                        <textarea name="monthly_comment" id="monthly_comment" class="form-control" rows="3">{{ $invoice->monthly_comment }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Hidden calculation fields -->
            <input type="hidden" name="invoice_amount" id="invoice_amount" value="{{ $invoice->invoice_amount }}">
            <input type="hidden" name="gross_bill_amount" id="gross_bill_amount" value="{{ $invoice->gross_bill_amount }}">
            <input type="hidden" name="grand_total" id="grand_total" value="{{ $invoice->grand_total }}">
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    <label class="form-check-label">
                        <input type="checkbox" name="send_mail" id="send_mail" class="form-check-input"
                               {{ $invoice->send_mail ? 'checked' : '' }}>
                        Send Invoice via Email
                    </label>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Update Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('after_scripts')
<script>
let employeeIndex = {{ count($invoice->invoiceEmployees) }};
let chargeIndex = {{ count($invoice->additionalCharges) }};
let taxIndex = {{ count($invoice->taxes) }};
let serviceTaxIndex = {{ count($invoice->serviceTaxDetails) }};

$(document).ready(function() {
    // Calculate initial totals
    calculateTotals();
    
    // Same JavaScript as create form
    // ... (copy all the JavaScript from create.blade.php)
});

// Include same JavaScript functions from create form
// calculateTotals(), populateEmployeeTable(), etc.
</script>
@endpush

@endsection