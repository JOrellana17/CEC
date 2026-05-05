<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $description
 */
class Permission extends SpatiePermission
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    /**
     * Get the roles that belong to the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withTimestamps();
    }

    /**
     * Get the users that have this permission directly.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')
            ->withTimestamps();
    }
}