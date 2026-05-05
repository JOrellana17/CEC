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
 * @property string $invoice_number
 * @property int|null $booking_id
 * @property int|null $reservation_id
 * @property int|null $guest_id
 * @property float $room_charges
 * @property float $service_charges
 * @property float $food_charges
 * @property float $other_charges
 * @property float $subtotal
 * @property float $discount_amount
 * @property float $tax_amount
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $due_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_date
 */
class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'booking_id',
        'reservation_id',
        'guest_id',
        'room_charges',
        'service_charges',
        'food_charges',
        'other_charges',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'refund_amount',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'notes',
        'terms',
        'created_by',
    ];

    protected $casts = [
        'room_charges' => 'decimal:2',
        'service_charges' => 'decimal:2',
        'food_charges' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'issue_date' => 'datetime',
        'due_date' => 'datetime',
        'paid_date' => 'datetime',
    ];

    /**
     * Boot method to generate invoice number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(static::max('id') + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the booking that owns the invoice.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the guest that owns the invoice.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the reservation that owns the invoice.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Get the user who created the invoice.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment.
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latest();
    }

    /**
     * Scope to get invoices by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::now());
    }

    /**
     * Calculate invoice totals.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = (float)($this->room_charges + $this->service_charges +
                          $this->food_charges + $this->other_charges);
        $this->total_amount = (float)($this->subtotal - $this->discount_amount + $this->tax_amount);
        $this->due_amount = (float)($this->total_amount - $this->paid_amount);
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    /**
     * Check if invoice can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->paid_date = Carbon::now();
        $this->paid_amount = (float)$this->total_amount;
        $this->due_amount = 0.0;
        $this->save();
    }
}