<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'phone',
        'nationality',
        'document_id',
        'notes',
        'incident_notes',
        'is_vip',
        'is_frequent',
        'is_blacklisted',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'is_frequent' => 'boolean',
        'is_blacklisted' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the bookings for the guest.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the reservations for the guest.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the invoices for the guest.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payments for the guest.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the full name of the guest.
     */
    public function getFullNameAttribute(): string
    {
        $fullName = $this->attributes['full_name'] ?? null;

        if ($fullName) {
            return $fullName;
        }

        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the initials of the guest.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Scope to get only active guests.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get VIP guests.
     */
    public function scopeVip($query)
    {
        return $query->where('is_vip', true);
    }

    /**
     * Get the total amount spent by the guest.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->bookings()->sum('total_amount');
    }

    /**
     * Get the total number of stays.
     */
    public function getTotalStaysAttribute(): int
    {
        return $this->bookings()->where('booking_status', 'checked_out')->count();
    }

    /**
     * Get the last booking.
     */
    public function lastBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->latest('check_in_date');
    }
}
