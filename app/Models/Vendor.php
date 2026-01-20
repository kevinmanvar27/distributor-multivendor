<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'store_description',
        'store_logo',
        'store_banner',
        'business_email',
        'business_phone',
        'business_address',
        'city',
        'state',
        'country',
        'postal_code',
        'gst_number',
        'pan_number',
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_account_holder_name',
        'commission_rate',
        'status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'is_featured',
        'priority',
        'social_links',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commission_rate' => 'decimal:2',
        'approved_at' => 'datetime',
        'is_featured' => 'boolean',
        'social_links' => 'array',
        'settings' => 'array',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->store_slug)) {
                $vendor->store_slug = Str::slug($vendor->store_name);
            }
            
            // Ensure unique slug
            $originalSlug = $vendor->store_slug;
            $count = 1;
            while (static::where('store_slug', $vendor->store_slug)->exists()) {
                $vendor->store_slug = $originalSlug . '-' . $count;
                $count++;
            }
        });
    }

    /**
     * Get the user that owns the vendor.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved the vendor.
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the products for the vendor.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the categories for the vendor.
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the staff members for the vendor.
     */
    public function staff()
    {
        return $this->hasMany(VendorStaff::class);
    }

    /**
     * Get the permissions for the vendor.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'vendor_permissions');
    }

    /**
     * Check if vendor is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if vendor is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if vendor is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if vendor is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if vendor has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        // Check if permission exists in vendor_permissions
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if vendor has any of the specified permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Get the store logo URL.
     */
    public function getStoreLogoUrlAttribute()
    {
        if ($this->store_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $this->store_logo)) {
            return asset('storage/vendor/' . $this->store_logo);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->store_name) . '&background=0D8ABC&color=fff&size=200';
    }

    /**
     * Get the store banner URL.
     */
    public function getStoreBannerUrlAttribute()
    {
        if ($this->store_banner && \Illuminate\Support\Facades\Storage::disk('public')->exists('vendor/' . $this->store_banner)) {
            return asset('storage/vendor/' . $this->store_banner);
        }
        
        return null;
    }

    /**
     * Scope for approved vendors.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for pending vendors.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for featured vendors.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get total revenue for the vendor.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->products()
            ->join('proforma_invoices', function ($join) {
                $join->whereJsonContains('proforma_invoices.invoice_data->cart_items', ['vendor_id' => $this->id]);
            })
            ->where('proforma_invoices.status', 'delivered')
            ->sum('proforma_invoices.total_amount');
    }

    /**
     * Get total products count.
     */
    public function getTotalProductsAttribute()
    {
        return $this->products()->count();
    }
}
