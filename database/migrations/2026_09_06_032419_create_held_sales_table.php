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
        Schema::create('held_sales', function (Blueprint $table) {
            $table->id();
            $table->string('hold_number', 50);
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 5, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->json('items_json'); // Held cart items snapshot
            $table->timestamp('created_at')->useCurrent();
            $table->index('hold_number', 'idx_held_sales_hold_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('held_sales');
    }
};
