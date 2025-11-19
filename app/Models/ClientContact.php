<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'contact_no',
        'designation_id',
        'email',
        'send_sms',
        'send_email',
    ];

    protected $casts = [
        'send_sms' => 'boolean',
        'send_email' => 'boolean',
    ];

    /**
     * Get the client that owns the contact.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the designation for the contact.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Scope a query to only include contacts that should receive SMS.
     */
    public function scopeReceiveSms($query)
    {
        return $query->where('send_sms', true);
    }

    /**
     * Scope a query to only include contacts that should receive emails.
     */
    public function scopeReceiveEmail($query)
    {
        return $query->where('send_email', true);
    }
}