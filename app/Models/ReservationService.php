<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationService extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
        'subtotal',
        'service_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'service_date' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function calculateTotal(): void
    {
        $this->total_price = number_format((float) ($this->quantity * $this->unit_price), 2, '.', '');
        $this->subtotal = $this->total_price;
    }
}
