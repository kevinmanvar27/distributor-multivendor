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
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            
            // Drop the existing unique constraint
            $table->dropUnique(['user_id', 'product_id']);
            
            // Make user_id nullable to support guest carts
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add session_id for guest users
            $table->string('session_id')->nullable()->after('user_id');
            
            // Add new unique constraints for both authenticated and guest users
            $table->unique(['user_id', 'product_id'], 'shopping_cart_items_user_product_unique');
            $table->unique(['session_id', 'product_id'], 'shopping_cart_items_session_product_unique');
            
            // Re-add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopping_cart_items', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            
            // Drop the new unique constraints
            $table->dropUnique('shopping_cart_items_user_product_unique');
            $table->dropUnique('shopping_cart_items_session_product_unique');
            
            // Restore the original unique constraint
            $table->unique(['user_id', 'product_id']);
            
            // Remove session_id column
            $table->dropColumn('session_id');
            
            // Make user_id not nullable again
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            // Re-add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};