<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property bool $is_active
 * @property int|null $role_id
 * @property string $status
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'role_id',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * Get the primary role linked by role_id.
     */
    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the permissions through roles.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            return $this->roles()
                ->whereKey($role->getKey())
                ->exists();
        }

        if ($role instanceof Collection) {
            return $role->contains(fn ($item) => $this->hasRole($item));
        }

        if (is_array($role)) {
            foreach ($role as $item) {
                if ($this->hasRole($item)) {
                    return true;
                }
            }

            return false;
        }

        return $this->roles()
            ->where(function ($query) use ($role) {
                $query->where('name', $role)
                    ->orWhere('slug', $role);
            })
            ->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission)
                ->orWhere('slug', $permission);
        })->exists()) {
            return true;
        }

        return $this->permissions()
            ->where(function ($query) use ($permission) {
                $query->where('name', $permission)
                    ->orWhere('slug', $permission);
            })
            ->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissions) {
            $query->whereIn('name', $permissions)
                ->orWhereIn('slug', $permissions);
        })->exists() || $this->permissions()
            ->whereIn('name', $permissions)
            ->orWhereIn('slug', $permissions)
            ->exists();
    }

    /**
     * Get all permissions as array.
     */
    public function getAllPermissions(): array
    {
        $permissions = [];
        
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->slug;
            }
        }
        
        foreach ($this->permissions as $permission) {
            $permissions[] = $permission->slug;
        }
        
        return array_unique($permissions);
    }

    /**
     * Get the bookings created by this user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    /**
     * Alias for user activity checks.
     */
    public function bookingActivities(): HasMany
    {
        return $this->bookings();
    }

    /**
     * Get the audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }
}
