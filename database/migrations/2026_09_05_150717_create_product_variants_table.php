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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('sku', 50)->nullable()->unique();
            $table->string('barcode', 50)->nullable()->unique();
            $table->decimal('cost', 14, 2)->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('stock', 14, 3)->default(0);
            $table->timestamps();
            $table->index('product_id', 'idx_variants_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
