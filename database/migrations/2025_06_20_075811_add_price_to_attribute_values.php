<?php
// database/migrations/2025_06_XX_000000_add_price_to_attribute_values.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToAttributeValues extends Migration
{
    public function up()
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->decimal('price', 10, 2)
                  ->default(0)
                  ->after('value');
        });
    }

    public function down()
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
