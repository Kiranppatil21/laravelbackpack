<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

echo "Testing Invoice Creation...\n\n";

// Get a test client
$client = Client::first();
if (!$client) {
    echo "✗ No clients found! Run seed-sample-data.php first.\n";
    exit(1);
}

echo "✓ Using client: {$client->name} (ID: {$client->id})\n";

// Get some employees for this client
$employees = Employee::where('client_id', $client->id)->limit(2)->get();
echo "✓ Found {$employees->count()} employees for this client\n";

try {
    DB::beginTransaction();
    
    // Create test invoice
    $invoiceNo = ClientInvoice::generateInvoiceNumber($client->id, '2025-12');
    
    $invoice = ClientInvoice::create([
        'invoice_no' => $invoiceNo,
        'client_id' => $client->id,
        'month' => '2025-12',
        'bill_date' => now()->format('Y-m-d'),
        'invoice_amount' => 50000.00,
        'other_amount_with_tax' => 0,
        'other_amount_without_tax' => 0,
        'service_charge_percent' => 10,
        'service_charge_amount' => 5000.00,
        'discount_percent' => 0,
        'discount_amount' => 0,
        'cst_amount' => 0,
        'gross_bill_amount' => 55000.00,
        'grand_total' => 55000.00,
        'comments' => 'Test invoice created via script',
        'monthly_comment' => 'December 2025 billing',
        'send_mail' => false,
    ]);
    
    echo "✓ Invoice created: {$invoice->invoice_no} (ID: {$invoice->id})\n";
    
    // Add employees to invoice
    foreach ($employees as $emp) {
        $invoice->invoiceEmployees()->create([
            'employee_id' => $emp->id,
            'duty_days' => 26,
            'overtime_hours' => 0,
            'daily_rate' => 1000.00,
            'overtime_rate' => 0,
            'payment' => 26000.00,
            'overtime_payment' => 0,
            'total_payment' => 26000.00,
        ]);
    }
    
    echo "✓ Added {$employees->count()} employees to invoice\n";
    
    DB::commit();
    
    echo "\n✅ Invoice created successfully!\n";
    echo "\nInvoice Details:\n";
    echo "- Invoice No: {$invoice->invoice_no}\n";
    echo "- Client: {$client->name}\n";
    echo "- Month: {$invoice->month}\n";
    echo "- Grand Total: ₹" . number_format($invoice->grand_total, 2) . "\n";
    echo "\nYou can view it at: http://127.0.0.1:8001/admin/client-invoice\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n✗ Error creating invoice:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
