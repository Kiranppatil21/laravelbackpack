<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeUniformAllocation extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'item_type',      // Type of uniform (Shirt, Pants, etc.)
        'size',           // Size (S, M, L, XL, etc.)
        'date_issued',    // Date when issued
        'condition',      // Condition (new, good, fair, poor)
        'notes',          // Additional notes
        'tenant_uuid',
    ];

    protected $casts = [
        'date_issued' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function getUniformTypes()
    {
        return [
            'Shirt' => 'Shirt',
            'Pants' => 'Pants',
            'Belt' => 'Belt',
            'Cap' => 'Cap',
            'Shoes' => 'Shoes',
            'Tie' => 'Tie',
            'Jacket' => 'Jacket',
            'Badge' => 'Badge',
            'Whistle' => 'Whistle',
            'Torch' => 'Torch',
            'Other' => 'Other',
        ];
    }

    public static function getConditions()
    {
        return [
            'new' => 'New',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
        ];
    }

    public static function getSizes()
    {
        return [
            'XS' => 'XS',
            'S' => 'S',
            'M' => 'M',
            'L' => 'L',
            'XL' => 'XL',
            'XXL' => 'XXL',
            'XXXL' => 'XXXL',
            'Free Size' => 'Free Size',
        ];
    }
}