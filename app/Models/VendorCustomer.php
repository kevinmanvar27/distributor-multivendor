<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCustomer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id',
        'user_id',
        'first_invoice_id',
    ];

    /**
     * Get the vendor.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user (customer).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the first invoice that created this relationship.
     */
    public function firstInvoice()
    {
        return $this->belongsTo(ProformaInvoice::class, 'first_invoice_id');
    }

    /**
     * Add a customer to a vendor if not already exists.
     * 
     * @param int $vendorId
     * @param int $userId
     * @param int|null $invoiceId
     * @return VendorCustomer
     */
    public static function addCustomerToVendor($vendorId, $userId, $invoiceId = null)
    {
        return static::firstOrCreate(
            [
                'vendor_id' => $vendorId,
                'user_id' => $userId,
            ],
            [
                'first_invoice_id' => $invoiceId,
            ]
        );
    }
}
