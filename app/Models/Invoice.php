<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    protected $table = 'invoices';
    protected $guarded = ['id'];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'metadata' => 'array',
        'gst_amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
