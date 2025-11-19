<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAcquaintance extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'details',     // Using existing field name - will store JSON or structured text
        'tenant_uuid',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function getRelationshipTypes()
    {
        return [
            'emergency_contact' => 'Emergency Contact',
            'reference' => 'Reference',
            'friend' => 'Friend',
            'neighbor' => 'Neighbor',
            'relative' => 'Relative',
            'colleague' => 'Colleague',
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