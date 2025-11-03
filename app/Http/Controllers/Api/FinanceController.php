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

        // compute totals first
        $gst = 0; $tds = 0; $total = 0;
        foreach ($data['items'] as $item) {
            $lineTotal = $item['qty'] * $item['unit_price'];
            $tax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
            $gst += $tax;
            $total += $lineTotal + $tax;
        }

        // adapt to existing invoices table which uses `date` and `total` columns
        $date = $data['issued_date'] ?? $data['date'] ?? now()->toDateString();

        $invoice = Invoice::create([
            'client_id' => $data['client_id'] ?? null,
            'date' => $date,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'issued',
            'total' => $total,
        ]);

        // create line items
        foreach ($data['items'] as $item) {
            $lineTotal = $item['qty'] * $item['unit_price'];
            $tax = ($lineTotal * ($item['tax_rate'] ?? 0)) / 100;
                $invoice->items()->create([
                'description' => $item['description'],
                'qty' => $item['qty'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'line_total' => $lineTotal + $tax,
            ]);
        }

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
        if ($paidTotal >= $invoice->total) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'partial']);
        }

        return response()->json($payment, 201);
    }

    /**
     * Generate statutory report (GST / TDS) for a date range.
     * Aggregates invoice line items and computes tax sums.
     */
    public function generateStatutoryReport(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|in:gst,tds,pf,esic',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
        ]);

        $start = $data['period_start'];
        $end = $data['period_end'];

        // For GST, compute tax per invoice line item where invoice.date within range
        if ($data['type'] === 'gst') {
            $rows = \DB::table('invoice_line_items')
                ->join('invoices', 'invoice_line_items.invoice_id', '=', 'invoices.id')
                ->whereBetween('invoices.date', [$start, $end])
                ->select('invoice_line_items.tax_rate',
                    \DB::raw('sum(invoice_line_items.qty * invoice_line_items.unit_price) as taxable_value'),
                    \DB::raw('sum((invoice_line_items.qty * invoice_line_items.unit_price) * invoice_line_items.tax_rate / 100.0) as tax_amount')
                )
                ->groupBy('invoice_line_items.tax_rate')
                ->get();

            $payload = [
                'summary' => [
                    'period_start' => $start,
                    'period_end' => $end,
                    'rows' => $rows,
                ],
            ];

        } else {
            // Not implemented in depth; return empty payload for other types
            $payload = ['summary' => ['period_start' => $start, 'period_end' => $end, 'rows' => []]];
        }

        $report = \App\Models\StatutoryReport::create([
            'type' => $data['type'],
            'period_start' => $start,
            'period_end' => $end,
            'payload' => $payload,
            'generated_by' => $request->user()->id ?? null,
        ]);

        return response()->json($report->fresh(), 201);
    }

    /**
     * Agency profitability summary for a date range.
     * For now calculates revenue (sum invoices.total) and returns a simple breakdown by client.
     */
    public function profitability(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'client_id' => 'nullable|integer',
        ]);

        $q = \DB::table('invoices')->whereBetween('date', [$data['period_start'], $data['period_end']]);
        if (! empty($data['client_id'])) {
            $q->where('client_id', $data['client_id']);
        }

        $revenue = (float) $q->sum('total');

        $byClient = \DB::table('invoices')
            ->select('client_id', \DB::raw('sum(total) as revenue'))
            ->whereBetween('date', [$data['period_start'], $data['period_end']])
            ->groupBy('client_id')
            ->get();

        $payload = [
            'revenue' => $revenue,
            'costs' => 0,
            'gross_margin' => $revenue,
            'margin_percent' => $revenue ? 100.0 : 0.0,
            'by_client' => $byClient,
        ];

        return response()->json($payload);
    }
}
