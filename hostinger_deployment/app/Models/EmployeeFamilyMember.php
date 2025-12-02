<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFamilyMember extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'age',
        'phone_no',       // Using existing field name
        'is_nominee',     // Using existing field name
        'tenant_uuid',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function getRelationshipTypes()
    {
        return [
            'father' => 'Father',
            'mother' => 'Mother',
            'spouse' => 'Spouse',
            'son' => 'Son',
            'daughter' => 'Daughter',
            'brother' => 'Brother',
            'sister' => 'Sister',
            'other' => 'Other',
        ];
    }

    /**
     * Get relationship display name
     */
    public function getRelationshipDisplayAttribute(): string
    {
        return self::getRelationshipTypes()[$this->relationship] ?? $this->relationship;
    }
}