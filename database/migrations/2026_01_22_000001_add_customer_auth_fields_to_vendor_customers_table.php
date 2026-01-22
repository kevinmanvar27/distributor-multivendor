<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds authentication fields to vendor_customers table
     * to allow vendors to create customers with login credentials.
     * Customers created by a vendor can only see that vendor's products.
     */
    public function up(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            $table->string('name')->after('user_id')->nullable();
            $table->string('email')->after('name')->nullable();
            $table->string('password')->after('email')->nullable();
            $table->string('mobile_number', 20)->after('password')->nullable();
            $table->text('address')->after('mobile_number')->nullable();
            $table->string('city', 100)->after('address')->nullable();
            $table->string('state', 100)->after('city')->nullable();
            $table->string('postal_code', 20)->after('state')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('postal_code');
            $table->boolean('is_active')->default(true)->after('discount_percentage');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->rememberToken()->after('last_login_at');
            
            // Make user_id nullable since vendor can create customer without linking to users table
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add unique constraint for email per vendor (same email can exist for different vendors)
            $table->unique(['vendor_id', 'email'], 'vendor_customer_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            $table->dropUnique('vendor_customer_email_unique');
            $table->dropColumn([
                'name',
                'email', 
                'password',
                'mobile_number',
                'address',
                'city',
                'state',
                'postal_code',
                'discount_percentage',
                'is_active',
                'last_login_at',
                'remember_token'
            ]);
        });
    }
};
