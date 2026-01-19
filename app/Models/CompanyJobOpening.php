<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class CompanyJobOpening extends Model
{
    use HasFactory;
    use CrudTrait;

    protected $fillable = [
        'title',
        'department',
        'location',
        'type',
        'experience_level',
        'description',
        'requirements',
        'salary_range',
        'status',
        'contact_email',
        'priority',
        'application_deadline',
    ];

    protected $casts = [
        'requirements' => 'array',
        'application_deadline' => 'date',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, $department)
    {
        if ($department && $department !== 'all') {
            return $query->where('department', $department);
        }
        return $query;
    }

    public function scopeByLocation($query, $location)
    {
        if ($location && $location !== 'all') {
            return $query->where('location', $location);
        }
        return $query;
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getPostedAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getIsExpiredAttribute()
    {
        if (!$this->application_deadline) {
            return false;
        }
        return Carbon::now()->isAfter($this->application_deadline);
    }

    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            1 => 'normal',
            2 => 'high', 
            3 => 'urgent',
            default => 'normal'
        };
    }

    // Static methods
    public static function getDepartments()
    {
        return self::active()->distinct('department')->pluck('department')->sort()->values();
    }

    public static function getLocations()
    {
        return self::active()->distinct('location')->pluck('location')->sort()->values();
    }

    public static function getJobTypes()
    {
        return [
            'full-time' => 'Full Time',
            'part-time' => 'Part Time',
            'contract' => 'Contract',
            'internship' => 'Internship'
        ];
    }

    public static function getStatusOptions()
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'closed' => 'Closed'
        ];
    }

    // Mutators for Backpack CRUD
    public function setRequirementsAttribute($value)
    {
        if (is_string($value)) {
            // Try to decode JSON string
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->attributes['requirements'] = $value;
            } else {
                // If not valid JSON, create JSON array from string
                $this->attributes['requirements'] = json_encode([$value]);
            }
        } elseif (is_array($value)) {
            $this->attributes['requirements'] = json_encode($value);
        } else {
            $this->attributes['requirements'] = json_encode([]);
        }
    }

    public function getRequirementsForEditAttribute()
    {
        if (is_array($this->requirements)) {
            return json_encode($this->requirements, JSON_PRETTY_PRINT);
        }
        return $this->requirements;
    }
}
