<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'employee_id', 'tenant_uuid', 'period_start', 'period_end', 'gross', 'net', 'breakdown'
    ];

    protected $casts = [
        'breakdown' => 'array'
    ];
}
