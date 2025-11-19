<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceServiceTaxDetail extends Model
{
    use \App\Models\Concerns\BelongsToTenant;

    protected $table = 'invoice_service_tax_details';
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }
}
