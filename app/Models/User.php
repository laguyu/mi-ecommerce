<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'is_admin', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
        private const ROLE_PERMISSIONS = [
                'admin' => ['manage_users', 'manage_products', 'manage_categories', 'manage_site_settings', 'view_admin_orders', 'delete_categories', 'delete_products'],
            'editor' => ['manage_products', 'manage_categories', 'view_admin_orders'],
            'soporte' => ['view_admin_orders'],
            'customer' => [],
        ];

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
            'is_admin' => 'boolean',
            'role' => 'string',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites')
            ->withTimestamps()
            ->orderByDesc('favorites.created_at');
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = self::ROLE_PERMISSIONS[$this->role] ?? [];

        return in_array($permission, $permissions, true);
    }

    public function permissionList(): array
    {
        return self::ROLE_PERMISSIONS[$this->role] ?? [];
    }
}
