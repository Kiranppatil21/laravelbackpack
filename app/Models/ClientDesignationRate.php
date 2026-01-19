<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDesignationRate extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'client_designation_rates';

    protected $fillable = [
        'client_id',
        'designation',
        'client_rate_per_day',
        'agency_rate_per_day',
        'client_ot_rate_per_hour',
        'agency_ot_rate_per_hour',
    ];

    protected $casts = [
        'client_rate_per_day' => 'decimal:2',
        'agency_rate_per_day' => 'decimal:2',
        'client_ot_rate_per_hour' => 'decimal:2',
        'agency_ot_rate_per_hour' => 'decimal:2',
    ];

    /**
     * Relationship to Client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Available designations
     */
    public static function availableDesignations(): array
    {
        return [
            'Security Guard',
            'Supervisor',
            'Manager',
            'Officer',
            'Executive',
            'Watchman',
            'Bouncer',
        ];
    }
}
