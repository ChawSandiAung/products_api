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
    Schema::create('variants', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        $table->decimal('carat', 5, 2)->nullable(); // carat like 18.00
        $table->enum('metal_type', ['gold', 'white_gold', 'platinum']);
        $table->decimal('price', 10, 2);
        $table->unsignedInteger('stock')->default(0);
        $table->string('sku')->unique();
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
