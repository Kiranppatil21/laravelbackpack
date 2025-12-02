<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id', 'visit_log_id', 'host_id', 'feedback_type', 'rating',
        'comments', 'responses', 'anonymous', 'ip_address'
    ];

    protected $casts = [
        'responses' => 'array',
        'anonymous' => 'boolean',
    ];

    // Relationships
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function visitLog(): BelongsTo
    {
        return $this->belongsTo(VisitLog::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    // Scopes
    public function scopePositive($query)
    {
        return $query->where('rating', '>=', 4);
    }

    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', 2);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('feedback_type', $type);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }

    public function isNegative(): bool
    {
        return $this->rating <= 2;
    }

    public function getResponse(string $question)
    {
        return $this->responses[$question] ?? null;
    }

    public function setResponse(string $question, $answer): void
    {
        $responses = $this->responses ?? [];
        $responses[$question] = $answer;
        $this->update(['responses' => $responses]);
    }
}