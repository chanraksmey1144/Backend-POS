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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();
            $table->foreignId('brand_id')->nullable()
                  ->constrained('brands')
                  ->nullOnDelete();
            $table->foreignId('unit_id')->nullable()
                  ->constrained('units')
                  ->nullOnDelete();
            $table->string('name');
            $table->string('sku', 50)->nullable()->unique();
            $table->string('barcode', 50)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('image_label', 20)->nullable();
            $table->string('image_color', 20)->nullable();
            $table->decimal('cost', 14, 2)->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('wholesale_price', 14, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->decimal('min_stock', 14, 3)->default(0);
            $table->decimal('max_stock', 14, 3)->nullable();
            $table->decimal('stock', 14, 3)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            // Indexes
            $table->index('name', 'idx_products_name');
            $table->index('status', 'idx_products_status');
            $table->index(['category_id', 'status'], 'idx_products_category_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
