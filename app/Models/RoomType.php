<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'base_price',
        'capacity',
        'max_capacity',
        'bed_type',
        'room_size',
        'amenities',
        'image',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'capacity' => 'integer',
        'max_capacity' => 'integer',
        'room_size' => 'integer',
        'amenities' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the rooms for the room type.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the active pricing for this room type.
     */
    public function activePricing(): HasOne
    {
        return $this->hasOne(RoomPricing::class)->where('is_active', true);
    }

    /**
     * Get all pricing for this room type.
     */
    public function pricing(): HasMany
    {
        return $this->hasMany(RoomPricing::class);
    }

    /**
     * Scope to get only active room types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the number of available rooms of this type.
     */
    public function getAvailableRoomsCount(): int
    {
        return $this->rooms()->where('status', 'available')->count();
    }

    /**
     * Get formatted amenities.
     */
    public function getFormattedAmenitiesAttribute(): string
    {
        if (empty($this->amenities)) {
            return '';
        }
        
        return implode(', ', $this->amenities);
    }
}