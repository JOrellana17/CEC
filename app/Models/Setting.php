<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        $setting = static::where('key', $key)->first();
        
        $data = [
            'value' => static::encodeValue($value, $type),
            'type' => $type,
        ];

        if ($description) {
            $data['description'] = $description;
        }

        if ($setting) {
            $setting->update($data);
        } else {
            $data['key'] = $key;
            static::create($data);
        }
    }

    /**
     * Cast value based on type.
     */
    protected static function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Encode value based on type.
     */
    protected static function encodeValue($value, string $type): string
    {
        return match ($type) {
            'json' => json_encode($value),
            'boolean' => $value ? 'true' : 'false',
            default => (string) $value,
        };
    }

    /**
     * Get decoded value attribute.
     */
    public function getDecodedValueAttribute()
    {
        return static::castValue($this->value, $this->type);
    }
}