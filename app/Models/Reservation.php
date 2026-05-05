<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $guest_id
 * @property int $room_id
 * @property \Illuminate\Support\Carbon $check_in
 * @property \Illuminate\Support\Carbon $check_out
 * @property int $guests_count
 * @property string $status
 * @property string|null $notes
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_id',
        'room_id',
        'check_in',
        'check_out',
        'guests_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'guests_count' => 'integer',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ReservationService::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'reservation_id');
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Invoice::class, 'reservation_id', 'invoice_id');
    }
}
