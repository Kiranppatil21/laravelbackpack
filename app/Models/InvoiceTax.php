<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTax extends Model
{
    use \App\Models\Concerns\BelongsToTenant;

    protected $table = 'invoice_taxes';
    protected $guarded = ['id'];

    protected $casts = [
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }
}
