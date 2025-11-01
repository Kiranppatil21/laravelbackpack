<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use \App\Models\Concerns\BelongsToTenant;
    use CrudTrait;
    use HasFactory;

    // Match the migration table name
    protected $table = 'attendances';

    protected $guarded = ['id'];

    protected $fillable = [
        'employee_id',
        'tenant_uuid',
        'check_in_at',
        'check_out_at',
        'check_in_type',
        'check_in_meta',
        'shift_id',
        'created_by',
    ];

    protected $casts = [
        'check_in_meta' => 'array',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
    // Use plural table name matching migration
