<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'device_token',
        'password',
        'user_role',
        'date_of_birth',
        'avatar',
        'address',
        'mobile_number',
        'is_approved',
        'discount_percentage',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'date_of_birth' => 'date',
            'discount_percentage' => 'decimal:2',
        ];
    }

    /**
     * Check if the user is a super admin.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->user_role === 'super_admin';
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->user_role === $role;
    }

    /**
     * Check if the user has any of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return in_array($this->user_role, $roles);
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if user has the permission through their roles
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        }

        // Also check direct user permissions
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Check if the user has any of the specified permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission($permissions)
    {
        // Super admins have all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if user has any of the permissions through their role
        $userRole = $this->user_role;
        $role = \App\Models\Role::where('name', $userRole)->first();
        
        if ($role) {
            return $role->permissions()->whereIn('name', $permissions)->exists();
        }

        return false;
    }

    /**
     * Get the URL of the user's avatar.
     *
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url('avatars/' . $this->avatar);
        }
        
        // Return a default avatar if none is set
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D8ABC&color=fff';
    }

    /**
     * Scope a query to only include staff members (super_admin, admin, editor).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('user_role', ['super_admin', 'admin', 'editor', 'vendor', 'vendor_staff']);
    }

    /**
     * Scope a query to exclude super admins.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNonSuperAdmin($query)
    {
        return $query->where('user_role', '!=', 'super_admin');
    }

    /**
     * Get the roles assigned to this user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }
    
    /**
     * Get the permissions directly assigned to this user.
     */
    public function permissions()
    {
        return $this->belongsToMany(\App\Models\Permission::class, 'user_permissions');
    }
    
    /**
     * Get the user groups that this user belongs to.
     */
    public function userGroups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_members');
    }
    
    /**
     * Check if the user is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->is_approved;
    }
    
    /**
     * Get the shopping cart items for the user.
     *
     * @return HasMany<ShoppingCartItem>
     */
    public function cartItems()
    {
        return $this->hasMany(ShoppingCartItem::class);
    }
    
    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class)->orderBy('created_at', 'desc');
    }
    
    /**
     * Get unread notifications for the user.
     */
    public function unreadNotifications()
    {
        return $this->hasMany(\App\Models\Notification::class)->where('read', false)->orderBy('created_at', 'desc');
    }
    
    /**
     * Get the wishlist items for the user.
     */
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }
    
    /**
     * Get the proforma invoices for the user.
     */
    public function proformaInvoices()
    {
        return $this->hasMany(ProformaInvoice::class);
    }
    
    /**
     * Get the attendance records for the user.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    /**
     * Get the salary records for the user.
     */
    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }
    
    /**
     * Get the active salary for the user.
     */
    public function activeSalary()
    {
        return $this->hasOne(Salary::class)->where('is_active', true)->latestOfMany('effective_from');
    }
    
    /**
     * Get the salary payments for the user.
     */
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
    
    /**
     * Get the vendor profile for the user.
     */
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }
    
    /**
     * Get the vendor staff record for the user.
     */
    public function vendorStaff()
    {
        return $this->hasOne(VendorStaff::class);
    }
    
    /**
     * Check if the user is a vendor.
     */
    public function isVendor(): bool
    {
        return $this->user_role === 'vendor';
    }
    
    /**
     * Check if the user is a vendor staff.
     */
    public function isVendorStaff(): bool
    {
        return $this->user_role === 'vendor_staff';
    }
    
    /**
     * Check if the user has vendor access (vendor or vendor staff).
     */
    public function hasVendorAccess(): bool
    {
        return $this->isVendor() || $this->isVendorStaff();
    }
    
    /**
     * Get the vendor for this user (either their own or the one they work for).
     * Use this method when you need to get the vendor regardless of user type.
     */
    public function getActiveVendor()
    {
        if ($this->isVendor()) {
            return $this->vendor()->first();
        }
        
        if ($this->isVendorStaff()) {
            $staffRecord = $this->vendorStaff()->first();
            return $staffRecord ? $staffRecord->vendor : null;
        }
        
        return null;
    }
    
    /**
     * Check if the user's vendor is approved.
     */
    public function isVendorApproved(): bool
    {
        $vendor = $this->getActiveVendor();
        return $vendor && $vendor->isApproved();
    }
    
    /**
     * Check if user has vendor permission.
     */
    public function hasVendorPermission($permission): bool
    {
        // If user is vendor owner, they have all permissions for their store
        if ($this->isVendor()) {
            return true;
        }
        
        // If user is vendor staff, check their specific permissions
        if ($this->isVendorStaff()) {
            $staffRecord = $this->vendorStaff()->first();
            return $staffRecord && $staffRecord->hasPermission($permission);
        }
        
        return false;
    }
    
    /**
     * Scope a query to only include vendors.
     */
    public function scopeVendors($query)
    {
        return $query->where('user_role', 'vendor');
    }
    
    /**
     * Scope a query to only include vendor staff.
     */
    public function scopeVendorStaff($query)
    {
        return $query->where('user_role', 'vendor_staff');
    }
}