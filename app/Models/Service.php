<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property float $price
 * @property bool $is_active
 */
class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'price_type',
        'unit',
        'category',
        'is_active',
        'is_available_24h',
        'available_from',
        'available_to',
        'icon',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_available_24h' => 'boolean',
        'available_from' => 'datetime:H:i',
        'available_to' => 'datetime:H:i',
    ];

    /**
     * Get the booking services for this service.
     */
    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    /**
     * Scope to get only active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get services by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get formatted price with unit.
     */
    public function getFormattedPriceAttribute(): string
    {
        return config('app.currency_symbol', '$') . number_format((float) $this->price, 2) . ' ' . ($this->unit ? '/ ' . $this->unit : '');
    }
}
