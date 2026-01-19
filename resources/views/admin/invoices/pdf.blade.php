<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .invoice-info { margin-bottom: 20px; }
        .info-section { display: inline-block; width: 48%; vertical-align: top; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { background-color: #f8f9fa; font-weight: bold; }
        .grand-total { background-color: #007bff; color: white; font-weight: bold; }
        .section-title { font-size: 16px; font-weight: bold; margin: 20px 0 10px 0; border-bottom: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <h2>{{ $invoice->invoice_no }}</h2>
    </div>

    <div class="invoice-info">
        <div class="info-section">
            <h3>Bill To:</h3>
            <strong>{{ $invoice->client->name }}</strong><br>
            {{ $invoice->client->address }}<br>
            {{ $invoice->client->city }}, {{ $invoice->client->state }} {{ $invoice->client->pincode }}<br>
            Phone: {{ $invoice->client->contact_number }}<br>
            Email: {{ $invoice->client->email }}
        </div>
        
        <div class="info-section" style="float: right;">
            <h3>Invoice Details:</h3>
            <strong>Invoice No:</strong> {{ $invoice->invoice_no }}<br>
            <strong>Month:</strong> {{ $invoice->month }}<br>
            <strong>Bill Date:</strong> {{ date('d/m/Y', strtotime($invoice->bill_date)) }}<br>
            <strong>Created:</strong> {{ $invoice->created_at->format('d/m/Y') }}
        </div>
        <div style="clear: both;"></div>
    </div>

    @if($invoice->invoiceEmployees->count() > 0)
    <div class="section-title">Employee Details</div>
    <table>
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Duty Days</th>
                <th>OT Hours</th>
                <th>Daily Rate</th>
                <th>OT Rate</th>
                <th>Payment</th>
                <th>OT Payment</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalEmployeeAmount = 0; @endphp
            @foreach($invoice->invoiceEmployees as $empInvoice)
            @php $totalEmployeeAmount += $empInvoice->total_payment; @endphp
            <tr>
                <td>{{ $empInvoice->employee->first_name }} {{ $empInvoice->employee->last_name }}</td>
                <td class="text-center">{{ number_format($empInvoice->duty_days, 2) }}</td>
                <td class="text-center">{{ number_format($empInvoice->overtime_hours, 2) }}</td>
                <td class="text-right">₹{{ number_format($empInvoice->daily_rate, 2) }}</td>
                <td class="text-right">₹{{ number_format($empInvoice->overtime_rate, 2) }}</td>
                <td class="text-right">₹{{ number_format($empInvoice->payment, 2) }}</td>
                <td class="text-right">₹{{ number_format($empInvoice->overtime_payment, 2) }}</td>
                <td class="text-right">₹{{ number_format($empInvoice->total_payment, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="7"><strong>Total Employee Amount:</strong></td>
                <td class="text-right"><strong>₹{{ number_format($totalEmployeeAmount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($invoice->additionalCharges->count() > 0)
    <div class="section-title">Additional Charges</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Comment</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalAdditionalCharges = 0; @endphp
            @foreach($invoice->additionalCharges as $charge)
            @php $totalAdditionalCharges += $charge->amount; @endphp
            <tr>
                <td>{{ date('d/m/Y', strtotime($charge->date)) }}</td>
                <td>{{ $charge->comment }}</td>
                <td class="text-right">₹{{ number_format($charge->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2"><strong>Total Additional Charges:</strong></td>
                <td class="text-right"><strong>₹{{ number_format($totalAdditionalCharges, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($invoice->taxes->count() > 0)
    <div class="section-title">Tax Details</div>
    <table>
        <thead>
            <tr>
                <th>Tax Type</th>
                <th>Tax %</th>
                <th>Tax Number</th>
                <th class="text-right">Tax Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalTax = 0; @endphp
            @foreach($invoice->taxes as $tax)
            @php $totalTax += $tax->tax_amount; @endphp
            <tr>
                <td>{{ $tax->tax_type }}</td>
                <td class="text-center">{{ $tax->tax_percent }}%</td>
                <td>{{ $tax->tax_no }}</td>
                <td class="text-right">₹{{ number_format($tax->tax_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3"><strong>Total Tax:</strong></td>
                <td class="text-right"><strong>₹{{ number_format($totalTax, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if($invoice->serviceTaxDetails->count() > 0)
    <div class="section-title">Service Tax Details</div>
    <table>
        <thead>
            <tr>
                <th>Service Type</th>
                <th>Amount</th>
                <th>Tax Type</th>
                <th>Tax %</th>
                <th class="text-right">Final Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $totalServiceTax = 0; @endphp
            @foreach($invoice->serviceTaxDetails as $serviceTax)
            @php $totalServiceTax += $serviceTax->final_amount; @endphp
            <tr>
                <td>{{ $serviceTax->service_type }}</td>
                <td class="text-right">₹{{ number_format($serviceTax->amount, 2) }}</td>
                <td>{{ $serviceTax->tax_type }}</td>
                <td class="text-center">{{ $serviceTax->tax_percent }}%</td>
                <td class="text-right">₹{{ number_format($serviceTax->final_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4"><strong>Total Service Tax:</strong></td>
                <td class="text-right"><strong>₹{{ number_format($totalServiceTax, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <!-- Invoice Summary -->
    <div class="section-title">Invoice Summary</div>
    <table>
        <tr>
            <td><strong>Invoice Amount:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->invoice_amount, 2) }}</td>
        </tr>
        @if($invoice->service_charge_amount > 0)
        <tr>
            <td><strong>Service Charge ({{ $invoice->service_charge_percent }}%):</strong></td>
            <td class="text-right">₹{{ number_format($invoice->service_charge_amount, 2) }}</td>
        </tr>
        @endif
        @if($invoice->other_amount_with_tax > 0)
        <tr>
            <td><strong>Other Amount (with tax):</strong></td>
            <td class="text-right">₹{{ number_format($invoice->other_amount_with_tax, 2) }}</td>
        </tr>
        @endif
        @if($invoice->other_amount_without_tax > 0)
        <tr>
            <td><strong>Other Amount (without tax):</strong></td>
            <td class="text-right">₹{{ number_format($invoice->other_amount_without_tax, 2) }}</td>
        </tr>
        @endif
        @if($invoice->cst_amount > 0)
        <tr>
            <td><strong>CST Amount:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->cst_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>Gross Bill Amount:</strong></td>
            <td class="text-right">₹{{ number_format($invoice->gross_bill_amount, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
        <tr>
            <td><strong>Discount ({{ $invoice->discount_percent }}%):</strong></td>
            <td class="text-right">-₹{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td><strong>GRAND TOTAL:</strong></td>
            <td class="text-right"><strong>₹{{ number_format($invoice->grand_total, 2) }}</strong></td>
        </tr>
    </table>

    @if($invoice->comments || $invoice->monthly_comment)
    <div class="section-title">Comments</div>
    @if($invoice->comments)
        <p><strong>Comments:</strong> {{ $invoice->comments }}</p>
    @endif
    @if($invoice->monthly_comment)
        <p><strong>Monthly Comment:</strong> {{ $invoice->monthly_comment }}</p>
    @endif
    @endif

    <div style="margin-top: 50px; text-align: center; color: #666; font-size: 12px;">
        Generated on {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>