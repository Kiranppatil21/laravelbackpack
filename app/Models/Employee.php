<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
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
    protected $guarded = ['id'];
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'client_id',
        'job_role',
        'shift',
        'kyc_status',
        'aadhar_path',
        'pan_path',
        'police_verification_path',
        'kyc_completed_at',
    ];

    protected $casts = [
        'shift' => 'array',
        'kyc_completed_at' => 'datetime',
    ];

    /**
     * The client this employee is assigned to (optional).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

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
