<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// Model imports
use App\Models\Purchase;
use App\Models\Sale;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'phone',
        'address',
        'status',           // ✅ Added
        'avatar',           // ✅ Added - এটা ছিল না বলে save হচ্ছিল না
        'email_verified_at',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Available roles (legacy - will be replaced by Spatie)
     */
    public const ROLES = [
        'Superadmin',
        'Admin',
        'User',
        'Employee',
        'Manager',
        'Cashier',
        'Supplier',
        'Customer',
        'Biller',
        'Stuff',
    ];

    /**
     * Scope: filter users by role (legacy)
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check exact role (legacy - use hasRole() from Spatie instead)
     */
    public function hasRoleLegacy(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Role helpers (legacy)
     */
    public function isSupplier(): bool
    {
        return $this->role === 'Supplier';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'Customer';
    }

    /**
     * Relationships
     */

    // Supplier → Purchases
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    // Customer → Sales
    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}