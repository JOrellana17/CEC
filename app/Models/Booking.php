<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $booking_number
 * @property int $guest_id
 * @property int $room_id
 * @property \Illuminate\Support\Carbon $check_in_date
 * @property \Illuminate\Support\Carbon $check_out_date
 * @property float $room_rate
 * @property float $discount_amount
 * @property float $discount_percentage
 * @property float $subtotal
 * @property float $tax_amount
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $due_amount
 * @property string $booking_status
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'guest_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'check_in_time',
        'check_out_time',
        'adults',
        'children',
        'room_rate',
        'discount_amount',
        'discount_percentage',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'booking_status',
        'payment_status',
        'payment_method',
        'special_requests',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'created_by',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'adults' => 'integer',
        'children' => 'integer',
        'room_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot method to generate booking number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = 'BK-' . date('Ymd') . '-' . str_pad(static::max('id') + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the guest that owns the booking.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room that owns the booking.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the user who cancelled the booking.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get the user who created the booking.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the services for the booking.
     */
    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    /**
     * Get the invoice for the booking.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get the payments for the booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the activities for the booking.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(BookingActivity::class);
    }

    /**
     * Scope to get bookings by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('booking_status', $status);
    }

    /**
     * Scope to get bookings by payment status.
     */
    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to get bookings for today.
     */
    public function scopeForToday($query)
    {
        return $query->where('check_in_date', Carbon::today());
    }

    /**
     * Scope to get bookings for today checkout.
     */
    public function scopeCheckoutToday($query)
    {
        return $query->where('check_out_date', Carbon::today());
    }

    /**
     * Scope to get active bookings.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('booking_status', ['pending', 'confirmed', 'checked_in']);
    }

    /**
     * Calculate number of nights.
     */
    public function getNightsAttribute(): int
    {
        return Carbon::parse($this->check_in_date)->diffInDays(Carbon::parse($this->check_out_date));
    }

    /**
     * Calculate the booking totals.
     */
    public function calculateTotals(): void
    {
        $nights = $this->nights;
        $subtotal = (float)($this->room_rate * $nights);
        
        $discount = 0.0;
        if ($this->discount_percentage > 0) {
            $discount = (float)($subtotal * ($this->discount_percentage / 100));
        } else {
            $discount = (float)$this->discount_amount;
        }
        
        $this->subtotal = (float)($subtotal - $discount);
        $this->tax_amount = (float)($this->subtotal * config('app.tax_rate', 0.1));
        $this->total_amount = (float)($this->subtotal + $this->tax_amount);
        $this->due_amount = (float)($this->total_amount - $this->paid_amount);
    }

    /**
     * Check if booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->booking_status, ['pending', 'confirmed']);
    }

    /**
     * Check if booking can be checked in.
     */
    public function canCheckIn(): bool
    {
        return $this->booking_status === 'confirmed' && 
               $this->check_in_date <= Carbon::today();
    }

    /**
     * Check if booking can be checked out.
     */
    public function canCheckOut(): bool
    {
        return $this->booking_status === 'checked_in';
    }
}