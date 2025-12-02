<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientInvoice extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    protected $table = 'client_invoices';

    protected $guarded = ['id'];

    protected $casts = [
        'bill_date' => 'date',
        'invoice_amount' => 'decimal:2',
        'other_amount_with_tax' => 'decimal:2',
        'other_amount_without_tax' => 'decimal:2',
        'service_charge_percent' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cst_amount' => 'decimal:2',
        'gross_bill_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'send_mail' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoiceEmployees(): HasMany
    {
        return $this->hasMany(InvoiceEmployee::class, 'invoice_id');
    }

    public function additionalCharges(): HasMany
    {
        return $this->hasMany(InvoiceAdditionalCharge::class, 'invoice_id');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceTax::class, 'invoice_id');
    }

    public function serviceTaxDetails(): HasMany
    {
        return $this->hasMany(InvoiceServiceTaxDetail::class, 'invoice_id');
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber($clientId, $month)
    {
        $client = Client::find($clientId);
        $monthYear = str_replace('-', '', $month); // Remove hyphen from YYYY-MM
        
        // Get next invoice number for this client and month
        $lastInvoice = self::where('client_id', $clientId)
            ->where('month', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastInvoice ? (int)substr($lastInvoice->invoice_no, -2) + 1 : 1;
        $sequence = str_pad($sequence, 2, '0', STR_PAD_LEFT);
        
        return 'INV-' . strtoupper(substr($client->name ?? 'CLI', 0, 3)) . $monthYear . $sequence;
    }

    /**
     * Calculate grand total based on all components
     */
    public function calculateGrandTotal()
    {
        $monthlyCharges = $this->invoiceEmployees->sum('total_payment');
        $serviceCharges = ($monthlyCharges * $this->service_charge_percent) / 100;
        $grossBill = $monthlyCharges + $serviceCharges + $this->other_amount_with_tax + $this->other_amount_without_tax;
        $discount = ($grossBill * $this->discount_percent) / 100;
        $taxAmount = $this->taxes->sum('tax_amount');
        $additionalChargesAmount = $this->additionalCharges->sum('amount');
        
        return $grossBill - $discount + $taxAmount + $additionalChargesAmount + $this->cst_amount;
    }

    /**
     * Recalculate all amounts and update the invoice
     */
    public function recalculate()
    {
        $monthlyCharges = $this->invoiceEmployees->sum('total_payment');
        $serviceChargeAmount = ($monthlyCharges * $this->service_charge_percent) / 100;
        $discountAmount = ($monthlyCharges * $this->discount_percent) / 100;
        $grossBillAmount = $monthlyCharges + $serviceChargeAmount + $this->other_amount_with_tax + $this->other_amount_without_tax;
        
        $this->update([
            'invoice_amount' => $monthlyCharges,
            'service_charge_amount' => $serviceChargeAmount,
            'discount_amount' => $discountAmount,
            'gross_bill_amount' => $grossBillAmount,
            'grand_total' => $this->calculateGrandTotal()
        ]);
    }

    /**
     * Get monthly attendance summary for employees assigned to client
     */
    public static function getEmployeeAttendanceSummary($clientId, $month)
    {
        // This method will fetch attendance data from your bulk attendance system
        // You'll need to adapt this based on your actual attendance table structure
        return Employee::whereHas('clients', function($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })->with(['attendanceMaster' => function($query) use ($month) {
            $query->where('month', $month);
        }])->get()->map(function($employee) {
            $attendance = $employee->attendanceMaster->first();
            return [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'duty_days' => $attendance->total_days ?? 0,
                'overtime_hours' => $attendance->total_overtime ?? 0,
                'daily_rate' => $employee->daily_salary ?? 0,
                'overtime_rate' => $employee->overtime_rate ?? 0,
                'payment' => ($attendance->total_days ?? 0) * ($employee->daily_salary ?? 0),
                'overtime_payment' => ($attendance->total_overtime ?? 0) * ($employee->overtime_rate ?? 0),
                'total_payment' => (($attendance->total_days ?? 0) * ($employee->daily_salary ?? 0)) + (($attendance->total_overtime ?? 0) * ($employee->overtime_rate ?? 0))
            ];
        });
    }
}
