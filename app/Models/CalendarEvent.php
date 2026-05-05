<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'event_uid',
        'source',
        'external_url',
        'start_date',
        'end_date',
        'last_synced_at',
        'raw_payload',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
