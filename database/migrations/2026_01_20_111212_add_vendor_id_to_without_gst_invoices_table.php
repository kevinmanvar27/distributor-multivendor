<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('without_gst_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->after('user_id');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('payment_status')->default('unpaid')->after('paid_amount');
            
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->index('vendor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('without_gst_invoices', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropColumn(['vendor_id', 'paid_amount', 'payment_status']);
        });
    }
};
