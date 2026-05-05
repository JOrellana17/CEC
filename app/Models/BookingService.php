<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $booking_id
 * @property int $service_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 */
class BookingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
        'service_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'service_date' => 'datetime',
    ];

    /**
     * Get the booking that owns the service.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the service.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the user who created the booking service.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate total price.
     */
    public function calculateTotal(): void
    {
        $this->total_price = number_format((float)($this->quantity * $this->unit_price), 2, '.', '');
    }
}