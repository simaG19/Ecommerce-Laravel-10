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
        Schema::table('carts', function (Blueprint $table) {
            // Make sure the attribute_options column exists and is properly typed
            if (!Schema::hasColumn('carts', 'attribute_options')) {
                $table->json('attribute_options')->nullable();
            } else {
                // Modify existing column to ensure it's JSON type
                $table->json('attribute_options')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Don't drop the column, just leave it as is
        });
    }
};
