<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\Agency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'employees';

    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $fillable = [
        // System fields
        'tenant_id',
        'agency_id', 
        'tenant_uuid',
        'hired_at',
        
        // Original fields
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'monthly_salary',
        'state',
        'client_id',
        'position',        // Added missing position field
        'job_role',
        'shift',
        'kyc_status',
        'aadhar_path',
        'pan_path',
        'police_verification_path',
        'kyc_completed_at',
        
        // New comprehensive fields
        'designation',
        'education',
        'father_name',
        'nationality',
        'current_address',
        'permanent_address',
        'same_address',
        'date_of_birth',
        'age',
        'gender',
        'marital_status',
        'photo_path',
        'shift_hour',
        'pf_no',
        'uan_no',
        'esic',
        'esic_percentage',
        'pf_percentage',
        'pt_charges_apply',
        'bank_name',
        'bank_branch',
        'account_no',
        'ifsc_code',
        'bank_phone_no',
        'account_holder_name',
        'old_company_name',
        'old_company_year',
        'reason_for_leaving',
        
        // Table fields for dynamic data (free Backpack compatible)
        'identity_proofs_data',
        'family_members_data',
        'acquaintances_data',
        'uniform_allocations_data',
        
        // Array fields for custom HTML forms
        'identity_proofs',
        'family_members',
        'acquaintances',
        'uniforms',
    ];

    protected $casts = [
        'shift' => 'array',
        'kyc_completed_at' => 'datetime',
        'monthly_salary' => 'decimal:2',
        'date_of_birth' => 'date',
        'same_address' => 'boolean',
        'pt_charges_apply' => 'boolean',
        'esic_percentage' => 'decimal:2',
        'pf_percentage' => 'decimal:2',
    ];

    /**
     * Convenience accessor to get full name.
     */
    public function getNameAttribute(): ?string
    {
        $first = $this->first_name ?? '';
        $last = $this->last_name ?? '';

        $full = trim($first . ' ' . $last);

        return $full === '' ? null : $full;
    }
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * The agency this employee belongs to.
     */
    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    /**
     * The client this employee is assigned to (optional).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Employee identity proofs (multiple)
     */
    public function identityProofs()
    {
        return $this->hasMany(EmployeeIdentityProof::class);
    }

    /**
     * Employee family members (multiple)
     */
    public function familyMembers()
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    /**
     * Employee acquaintances in Pune (multiple)
     */
    public function acquaintances()
    {
        return $this->hasMany(EmployeeAcquaintance::class);
    }

    /**
     * Employee uniform allocations (multiple)
     */
    public function uniformAllocations()
    {
        return $this->hasMany(EmployeeUniformAllocation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
