<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIdentityProof extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'identity_proof_type', // Using existing field name
        'identity_proof_no',   // Using existing field name
        'image_path',          // Using existing field name
        'tenant_uuid',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public static function getDocumentTypes()
    {
        return [
            'aadhar_card' => 'Aadhar Card',
            'pan_card' => 'PAN Card',
            'voter_id' => 'Voter ID',
            'driving_license' => 'Driving License',
            'passport' => 'Passport',
            'bank_passbook' => 'Bank Passbook',
            'other' => 'Other',
        ];
    }

    /**
     * Get document type display name
     */
    public function getDocumentTypeDisplayAttribute(): string
    {
        return self::getDocumentTypes()[$this->document_type] ?? $this->document_type;
    }
}