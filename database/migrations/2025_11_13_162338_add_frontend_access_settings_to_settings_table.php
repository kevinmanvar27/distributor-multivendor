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
            // Frontend access permission settings
            $table->string('frontend_access_permission')->default('open_for_all'); // open_for_all, registered_users_only, admin_approval_required
            $table->text('pending_approval_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Remove frontend access permission settings
            $table->dropColumn(['frontend_access_permission', 'pending_approval_message']);
        });
    }
};