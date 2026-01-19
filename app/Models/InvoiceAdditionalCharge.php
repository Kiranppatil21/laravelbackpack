<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAdditionalCharge extends Model
{
    use \App\Models\Concerns\BelongsToTenant;

    protected $table = 'invoice_additional_charges';
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }
}
