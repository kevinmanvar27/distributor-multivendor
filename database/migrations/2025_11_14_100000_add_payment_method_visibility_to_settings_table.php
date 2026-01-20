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
        Schema::table('settings', function (Blueprint $table) {
            // Add payment method visibility settings
            $table->boolean('show_online_payment')->default(true);
            $table->boolean('show_cod_payment')->default(true);
            $table->boolean('show_invoice_payment')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Remove payment method visibility settings
            $table->dropColumn(['show_online_payment', 'show_cod_payment', 'show_invoice_payment']);
        });
    }
};