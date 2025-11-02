<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'id_type', 'id_value', 'host_id', 'source',
    ];

    public function visitLogs(): HasMany
    {
        return $this->hasMany(VisitLog::class);
    }
}
