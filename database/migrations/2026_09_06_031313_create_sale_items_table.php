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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()
                  ->constrained('products')
                  ->nullOnDelete();
            $table->foreignId('variant_id')->nullable()
                  ->constrained('product_variants')
                  ->nullOnDelete();
            $table->string('name');
            $table->string('sku', 50)->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('cost', 14, 2)->default(0);
            $table->decimal('quantity', 14, 3)->default(1);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 5, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();
            // Indexes
            $table->index('sale_id', 'idx_sale_items_sale');
            $table->index('product_id', 'idx_sale_items_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
