<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ClientInvoiceRequest;
use App\Models\ClientInvoice;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeAttendanceMaster;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ClientInvoiceCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClientInvoiceCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ClientInvoice::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/client-invoice');
        CRUD::setEntityNameStrings('client invoice', 'client invoices');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('invoice_no')->label('Invoice No')->type('text');
        CRUD::column('client')->label('Client')->type('relationship')->attribute('name');
        CRUD::column('month')->label('Month')->type('text');
        CRUD::column('bill_date')->label('Bill Date')->type('date');
        CRUD::column('grand_total')->label('Grand Total')->type('number')->prefix('₹')->decimals(2);
        CRUD::column('send_mail')->label('Email Sent')->type('boolean');
        CRUD::column('created_at')->label('Created')->type('datetime');

        // Add Generate PDF button
        $this->crud->addButtonFromView('line', 'generate_invoice_pdf', 'generate_invoice_pdf', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClientInvoiceRequest::class);

        // Use custom create form
        $this->crud->setCreateView('admin.invoices.create');
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $this->crud->setUpdateView('admin.invoices.edit');
    }

    /**
     * Show custom invoice entry form
     */
    public function create()
    {
        $clients = Client::all();
        return view('admin.invoices.create', compact('clients'));
    }

    /**
     * Show edit form with existing invoice data
     */
    public function edit($id)
    {
        $invoice = ClientInvoice::with([
            'client', 
            'invoiceEmployees.employee', 
            'additionalCharges', 
            'taxes', 
            'serviceTaxDetails'
        ])->findOrFail($id);
        
        $clients = Client::all();
        
        return view('admin.invoices.edit', compact('invoice', 'clients'));
    }

    /**
     * Store new invoice
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Generate invoice number
            $invoiceNo = ClientInvoice::generateInvoiceNumber($request->client_id, $request->month);

            // Create main invoice
            $invoice = ClientInvoice::create([
                'invoice_no' => $invoiceNo,
                'client_id' => $request->client_id,
                'month' => $request->month,
                'bill_date' => $request->bill_date,
                'invoice_amount' => $request->invoice_amount ?? 0,
                'other_amount_with_tax' => $request->other_amount_with_tax ?? 0,
                'other_amount_without_tax' => $request->other_amount_without_tax ?? 0,
                'service_charge_percent' => $request->service_charge_percent ?? 0,
                'service_charge_amount' => $request->service_charge_amount ?? 0,
                'discount_percent' => $request->discount_percent ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'cst_amount' => $request->cst_amount ?? 0,
                'gross_bill_amount' => $request->gross_bill_amount ?? 0,
                'grand_total' => $request->grand_total ?? 0,
                'comments' => $request->comments,
                'monthly_comment' => $request->monthly_comment,
                'send_mail' => $request->has('send_mail'),
            ]);

            // Save invoice employees
            if ($request->has('employees')) {
                foreach ($request->employees as $emp) {
                    $invoice->invoiceEmployees()->create([
                        'employee_id' => $emp['employee_id'],
                        'duty_days' => $emp['duty_days'] ?? 0,
                        'overtime_hours' => $emp['overtime_hours'] ?? 0,
                        'daily_rate' => $emp['daily_rate'] ?? 0,
                        'overtime_rate' => $emp['overtime_rate'] ?? 0,
                        'payment' => $emp['payment'] ?? 0,
                        'overtime_payment' => $emp['overtime_payment'] ?? 0,
                        'total_payment' => $emp['total_payment'] ?? 0,
                    ]);
                }
            }

            // Save additional charges
            if ($request->has('additional_charges')) {
                foreach ($request->additional_charges as $charge) {
                    if (!empty($charge['amount'])) {
                        $invoice->additionalCharges()->create([
                            'date' => $charge['date'],
                            'amount' => $charge['amount'],
                            'comment' => $charge['comment'],
                        ]);
                    }
                }
            }

            // Save taxes
            if ($request->has('taxes')) {
                foreach ($request->taxes as $tax) {
                    if (!empty($tax['tax_percent'])) {
                        $invoice->taxes()->create([
                            'tax_type' => $tax['tax_type'],
                            'tax_percent' => $tax['tax_percent'],
                            'tax_amount' => $tax['tax_amount'],
                            'tax_no' => $tax['tax_no'],
                        ]);
                    }
                }
            }

            // Save service tax details
            if ($request->has('service_tax_details')) {
                foreach ($request->service_tax_details as $detail) {
                    if (!empty($detail['amount'])) {
                        $invoice->serviceTaxDetails()->create([
                            'amount' => $detail['amount'],
                            'service_type' => $detail['service_type'],
                            'tax_type' => $detail['tax_type'],
                            'tax_percent' => $detail['tax_percent'] ?? 0,
                            'final_amount' => $detail['final_amount'],
                            'comment' => $detail['comment'],
                        ]);
                    }
                }
            }

            // Recalculate totals
            $invoice->recalculate();

            DB::commit();

            // Send email if requested
            if ($request->has('send_mail')) {
                // TODO: Implement email sending logic
                // $this->sendInvoiceEmail($invoice);
            }

            return redirect()->route('client-invoice.index')
                ->with('success', 'Invoice created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update existing invoice
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $invoice = ClientInvoice::findOrFail($id);

            // Update main invoice
            $invoice->update([
                'client_id' => $request->client_id,
                'month' => $request->month,
                'bill_date' => $request->bill_date,
                'invoice_amount' => $request->invoice_amount ?? 0,
                'other_amount_with_tax' => $request->other_amount_with_tax ?? 0,
                'other_amount_without_tax' => $request->other_amount_without_tax ?? 0,
                'service_charge_percent' => $request->service_charge_percent ?? 0,
                'service_charge_amount' => $request->service_charge_amount ?? 0,
                'discount_percent' => $request->discount_percent ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'cst_amount' => $request->cst_amount ?? 0,
                'gross_bill_amount' => $request->gross_bill_amount ?? 0,
                'grand_total' => $request->grand_total ?? 0,
                'comments' => $request->comments,
                'monthly_comment' => $request->monthly_comment,
                'send_mail' => $request->has('send_mail'),
            ]);

            // Clear existing relationships
            $invoice->invoiceEmployees()->delete();
            $invoice->additionalCharges()->delete();
            $invoice->taxes()->delete();
            $invoice->serviceTaxDetails()->delete();

            // Re-save invoice employees
            if ($request->has('employees')) {
                foreach ($request->employees as $emp) {
                    $invoice->invoiceEmployees()->create([
                        'employee_id' => $emp['employee_id'],
                        'duty_days' => $emp['duty_days'] ?? 0,
                        'overtime_hours' => $emp['overtime_hours'] ?? 0,
                        'daily_rate' => $emp['daily_rate'] ?? 0,
                        'overtime_rate' => $emp['overtime_rate'] ?? 0,
                        'payment' => $emp['payment'] ?? 0,
                        'overtime_payment' => $emp['overtime_payment'] ?? 0,
                        'total_payment' => $emp['total_payment'] ?? 0,
                    ]);
                }
            }

            // Re-save additional charges
            if ($request->has('additional_charges')) {
                foreach ($request->additional_charges as $charge) {
                    if (!empty($charge['amount'])) {
                        $invoice->additionalCharges()->create([
                            'date' => $charge['date'],
                            'amount' => $charge['amount'],
                            'comment' => $charge['comment'],
                        ]);
                    }
                }
            }

            // Re-save taxes
            if ($request->has('taxes')) {
                foreach ($request->taxes as $tax) {
                    if (!empty($tax['tax_percent'])) {
                        $invoice->taxes()->create([
                            'tax_type' => $tax['tax_type'],
                            'tax_percent' => $tax['tax_percent'],
                            'tax_amount' => $tax['tax_amount'],
                            'tax_no' => $tax['tax_no'],
                        ]);
                    }
                }
            }

            // Re-save service tax details
            if ($request->has('service_tax_details')) {
                foreach ($request->service_tax_details as $detail) {
                    if (!empty($detail['amount'])) {
                        $invoice->serviceTaxDetails()->create([
                            'amount' => $detail['amount'],
                            'service_type' => $detail['service_type'],
                            'tax_type' => $detail['tax_type'],
                            'tax_percent' => $detail['tax_percent'] ?? 0,
                            'final_amount' => $detail['final_amount'],
                            'comment' => $detail['comment'],
                        ]);
                    }
                }
            }

            // Recalculate totals
            $invoice->recalculate();

            DB::commit();

            return redirect()->route('client-invoice.index')
                ->with('success', 'Invoice updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get employee attendance summary for AJAX request
     */
    public function getEmployeeAttendance(Request $request)
    {
        $clientId = $request->client_id;
        $month = $request->month;

        try {
            // Get employees assigned to this client
            $employees = Employee::where('client_id', $clientId)->get();
            
            $attendanceData = [];
            
            foreach ($employees as $employee) {
                // Get attendance data from bulk attendance system
                $attendance = EmployeeAttendanceMaster::where('employee_id', $employee->id)
                    ->where('month', $month)
                    ->first();

                $dutyDays = 0;
                $overtimeHours = 0;

                if ($attendance) {
                    // Calculate duty days and overtime from attendance details
                    $details = $attendance->details ?? collect();
                    $dutyDays = $details->sum('working_hours') / 8; // Assuming 8 hours per day
                    $overtimeHours = $details->sum('overtime_hours');
                }

                $dailyRate = $employee->monthly_salary ? ($employee->monthly_salary / 30) : 0;
                $overtimeRate = $dailyRate * 1.5; // 1.5x for overtime
                
                $payment = $dutyDays * $dailyRate;
                $overtimePayment = $overtimeHours * ($overtimeRate / 8); // Per hour rate
                $totalPayment = $payment + $overtimePayment;

                $attendanceData[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'duty_days' => round($dutyDays, 2),
                    'overtime_hours' => round($overtimeHours, 2),
                    'daily_rate' => round($dailyRate, 2),
                    'overtime_rate' => round($overtimeRate / 8, 2), // Per hour
                    'payment' => round($payment, 2),
                    'overtime_payment' => round($overtimePayment, 2),
                    'total_payment' => round($totalPayment, 2)
                ];
            }

            return response()->json([
                'success' => true,
                'employees' => $attendanceData,
                'total' => array_sum(array_column($attendanceData, 'total_payment'))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate invoice PDF
     */
    public function generatePDF($id)
    {
        $invoice = ClientInvoice::with([
            'client',
            'invoiceEmployees.employee',
            'additionalCharges',
            'taxes',
            'serviceTaxDetails'
        ])->findOrFail($id);

        return view('admin.invoices.pdf', compact('invoice'));
    }
}