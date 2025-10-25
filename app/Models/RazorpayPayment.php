<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class RazorpayPayment extends Model
{
    use CrudTrait;
    protected $table = 'razorpay_payments';
    protected $guarded = [];
    protected $casts = [
        'raw' => 'array',
        'last_retry_at' => 'datetime',
    ];
}
