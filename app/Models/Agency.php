<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'agencies';

    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    /**
     * Mass assignable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'details',
        'gst_number',
        'pan_number',
        'email',
        'phone',
        'registered_address',
        'communication_address',
        'company_type',
        'crn_number',
        'contact_person_name',
        'contact_person_email',
        'contact_person_phone',
        'contact_person_designation',
        'is_active',
        'services',
    ];
    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'services' => 'array',
        'is_active' => 'boolean',
    ];
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
    public function clients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Client::class, 'agency_id');
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class, 'agency_id');
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

    protected function setServicesAttribute($value)
    {
        // Convert comma-separated string to array and store as JSON
        if (is_string($value)) {
            $trimmed = array_filter(array_map('trim', explode(',', $value)));
            $this->attributes['services'] = json_encode($trimmed);
        } elseif (is_array($value)) {
            $this->attributes['services'] = json_encode($value);
        } else {
            $this->attributes['services'] = json_encode([]);
        }
    }

    protected function getServicesAttribute($value)
    {
        // Return as comma-separated string for textarea display
        if (!$value) {
            return '';
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? implode(', ', $decoded) : '';
    }
}
