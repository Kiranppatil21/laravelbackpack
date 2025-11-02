<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitLog extends Model
{
    use HasFactory;

    protected $table = 'visit_logs';

    protected $fillable = [
        'visitor_id', 'host_id', 'check_in_at', 'check_out_at', 'source', 'notes', 'external_id',
    ];

    protected $dates = ['check_in_at', 'check_out_at'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
