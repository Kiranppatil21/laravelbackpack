<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class RazorpayPayment extends Model
{
    use CrudTrait;

    protected $table = 'razorpay_payments';

    protected $guarded = [];

    protected $casts = [
        'raw' => 'array',
        'last_retry_at' => 'datetime',
    ];

    use \App\Models\Concerns\BelongsToTenant;
}
