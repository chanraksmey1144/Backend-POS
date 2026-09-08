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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('movement_date');
            $table->string('type', 20); // purchase | sale | return | damage | adjustment | transfer
            $table->decimal('quantity', 14, 3); // + incoming, - outgoing
            $table->decimal('before_stock', 14, 3)->nullable();
            $table->decimal('after_stock', 14, 3)->nullable();
            $table->string('reference', 100)->nullable(); // e.g. PO-2026-0001, INV-001
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Indexes
            $table->index(['product_id', 'movement_date'], 'idx_movements_product_date');
            $table->index('type', 'idx_movements_type');
            $table->index('warehouse_id', 'idx_movements_warehouse');
            $table->index('reference', 'idx_movements_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
