<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomPricing extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'season_name',
        'start_date',
        'end_date',
        'price',
        'extra_person_price',
        'child_price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'extra_person_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the room type that owns the pricing.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Scope to get active pricing.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get pricing for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }

    /**
     * Check if pricing is valid for a date range.
     */
    public function isValidForDates($startDate, $endDate): bool
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date <= $endDate && $this->end_date >= $startDate;
        }
        
        if ($this->start_date) {
            return $this->start_date <= $endDate;
        }
        
        if ($this->end_date) {
            return $this->end_date >= $startDate;
        }
        
        return true;
    }
}