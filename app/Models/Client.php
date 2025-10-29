<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $tenant_uuid
 */
class Client extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Add fields as needed by your app.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'tenant_id',
        'tenant_uuid',
        'agency_id',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'client_id');
    }
}
