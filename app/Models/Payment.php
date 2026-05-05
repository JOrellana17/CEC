<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $invoice_id
 * @property float $amount
 * @property string|null $notes
 * @property string $status
 */
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'booking_id',
        'guest_id',
        'amount',
        'payment_method',
        'transaction_id',
        'card_last_four',
        'card_type',
        'bank_name',
        'reference_number',
        'notes',
        'status',
        'payment_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Boot method to generate payment number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = 'PAY-' . date('Ymd') . '-' . str_pad(static::max('id') + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the invoice that owns the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the booking that owns the payment.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the guest that owns the payment.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the user who created the payment.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get payments by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get payments by method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope to get completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return config('app.currency_symbol', '$') . number_format((float) $this->amount, 2);
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed';
    }
}