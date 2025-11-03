<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    public function indexInvoices(Request $request)
    {
        $q = Invoice::query();

        if ($request->filled('client_id')) {
            $q->where('client_id', $request->client_id);
        }

        return $q->with('items','payments')->paginate(15);
    }

    public function storeInvoice(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'nullable|integer',
            'issued_date' => 'required|date',
            'due_date' => 'nullable|date',
            'currency' => 'string|nullable',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'numeric|min:0',
            'items.*.unit_price' => 'numeric|min:0',
            'items.*.tax_rate' => 'numeric|min:0',
        ]);

        $invoice = Invoice::create([
            'client_id' => $data['client_id'] ?? null,
            'issued_date' => $data['issued_date'],
            'due_date' => $data['due_date'] ?? null,
            'currency' => $data['currency'] ?? 'INR',
            'status' => 'issued',
        ]);

        $gst = 0; $tds = 0; $total = 0;
        foreach ($data['items'] as $item) {
            $lineTotal = $item['qty'] * $item['unit_price'];
            $tax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
            $gst += $tax;
            $total += $lineTotal + $tax;
            $invoice->items()->create([
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'line_total' => $lineTotal + $tax,
            ]);
        }

        $invoice->update(['gst_amount' => $gst, 'tds_amount' => $tds, 'total_amount' => $total]);

        return response()->json($invoice->load('items','payments'), 201);
    }

    public function showInvoice(Invoice $invoice)
    {
        return $invoice->load('items','payments');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'method' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create([
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'] ?? now(),
            'method' => $data['method'] ?? null,
            'reference' => $data['reference'] ?? null,
        ]);

        // update invoice status
        $paidTotal = $invoice->payments()->sum('amount');
        if ($paidTotal >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return response()->json($payment, 201);
    }
}
