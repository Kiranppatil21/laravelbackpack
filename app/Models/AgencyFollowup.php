<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyFollowup extends Model
{
    use HasFactory;

    protected $table = 'agency_followups';

    protected $guarded = ['id'];

    protected $casts = [
        'attachments' => 'array',
        'followed_up_at' => 'datetime',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function leadPerson()
    {
        return $this->belongsTo(User::class, 'lead_person_id');
    }
}
