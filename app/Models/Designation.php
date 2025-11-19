<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'level',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'level' => 'integer',
    ];

    /**
     * Get the client contacts for this designation.
     */
    public function clientContacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    /**
     * Scope a query to only include active designations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}