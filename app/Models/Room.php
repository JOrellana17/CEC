<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $room_number
 * @property int $floor_id
 * @property int $room_type_id
 * @property float $price_per_night
 * @property int $capacity
 * @property string $status
 * @property string|null $room_status
 * @property bool $is_active
 * @property bool $is_smoking
 * @property bool $has_balcony
 */
class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_number',
        'floor_id',
        'room_type_id',
        'floor',
        'building',
        'price_per_night',
        'capacity',
        'max_capacity',
        'extra_person_price',
        'status',
        'room_status',
        'description',
        'image',
        'is_active',
        'is_smoking',
        'has_balcony',
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'extra_person_price' => 'decimal:2',
        'capacity' => 'integer',
        'max_capacity' => 'integer',
        'is_active' => 'boolean',
        'is_smoking' => 'boolean',
        'has_balcony' => 'boolean',
    ];

    /**
     * Get the floor that owns the room.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Get the floor record without colliding with the legacy floor attribute.
     */
    public function floorLevel(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    /**
     * Get the room type that owns the room.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the bookings for the room.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the current active booking for the room.
     */
    public function currentBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->whereIn('booking_status', ['confirmed', 'checked_in'])
            ->where('check_in_date', '<=', now()->toDateString())
            ->where('check_out_date', '>=', now()->toDateString());
    }

    /**
     * Get room availabilities.
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(RoomAvailability::class);
    }

    /**
     * Scope to get only active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get available rooms.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get rooms by floor.
     */
    public function scopeByFloor($query, int $floorId)
    {
        return $query->where('floor_id', $floorId);
    }

    /**
     * Scope to get rooms by type.
     */
    public function scopeByType($query, int $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    /**
     * Check if room is available for booking.
     */
    public function isAvailableForDates($checkIn, $checkOut): bool
    {
        $bookings = $this->bookings()
            ->whereIn('booking_status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in_date', '<=', $checkIn)
                            ->where('check_out_date', '>=', $checkOut);
                    });
            })
            ->count();

        return $bookings === 0 && $this->status === 'available';
    }

    /**
     * Get the full room name with type.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->room_number} - {$this->roomType->name}";
    }

    /**
     * Calculate the per-night surcharge for guests above included capacity.
     */
    public function extraPersonChargeFor(int $guestCount): float
    {
        $extraGuests = max(0, $guestCount - (int) $this->capacity);

        return $extraGuests * (float) $this->extra_person_price;
    }
}
