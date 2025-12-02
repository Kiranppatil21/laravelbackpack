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
        'client_id',
        'item_name',   // Using existing field name instead of uniform_type
        'quantity',
        'rate',        // Using existing field name 
        'sub_total',   // Using existing field name
        'tenant_uuid',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'issue_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function getUniformTypes()
    {
        return [
            'shirt' => 'Shirt',
            'pant' => 'Pant',
            'belt' => 'Belt',
            'cap' => 'Cap',
            'shoes' => 'Shoes',
            'tie' => 'Tie',
            'jacket' => 'Jacket',
            'other' => 'Other',
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
        ];
    }

    /**
     * Get uniform type display name
     */
    public function getUniformTypeDisplayAttribute(): string
    {
        return self::getUniformTypes()[$this->uniform_type] ?? $this->uniform_type;
    }
}