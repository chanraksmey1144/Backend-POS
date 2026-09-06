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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('register_id')->nullable()->constrained('registers')->nullOnDelete();
            $table->dateTime('sale_date');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('paid', 14, 2)->default(0);
            $table->decimal('change', 14, 2)->default(0);
            $table->string('payment_method', 30)->default('cash');
            $table->string('status', 20)->default('completed');
            $table->string('payment_status', 20)->default('paid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // Indexes
            $table->index('sale_date', 'idx_sales_date');
            $table->index('status', 'idx_sales_status');
            $table->index('payment_status', 'idx_sales_payment_status');
            $table->index('customer_id', 'idx_sales_customer');
            $table->index('cashier_id', 'idx_sales_cashier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
