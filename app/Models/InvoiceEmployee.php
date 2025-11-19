<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceEmployee extends Model
{
    use \App\Models\Concerns\BelongsToTenant;

    protected $table = 'invoice_employees';
    protected $guarded = ['id'];

    protected $casts = [
        'overtime_hours' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'payment' => 'decimal:2',
        'overtime_payment' => 'decimal:2',
        'total_payment' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
