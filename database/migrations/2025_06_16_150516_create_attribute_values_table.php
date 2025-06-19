<?php
// database/migrations/2025_06_16_000002_create_attribute_values_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeValuesTable extends Migration
{
    public function up()
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_attribute_id')
                  ->constrained('product_attributes')
                  ->cascadeOnDelete();
            $table->string('value');      // e.g. “Red”, “2 kg”
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_values');
    }
}
