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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name');
            $table->string('sku', 50)->nullable();
            $table->decimal('cost', 14, 2)->default(0);
            $table->decimal('quantity', 14, 3)->default(0);
            $table->decimal('received_quantity', 14, 3)->default(0);
            $table->timestamp('created_at')->useCurrent();
            // Indexes
            $table->index('purchase_id', 'idx_purchase_items_purchase');
            $table->index('product_id', 'idx_purchase_items_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
