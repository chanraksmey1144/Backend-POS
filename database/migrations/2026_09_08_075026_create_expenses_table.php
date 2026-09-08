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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 50); // Rent | Utilities | Salaries | Supplies | Transportation | Maintenance | Marketing | Taxes | Other
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('payment_method', 30)->default('cash');
            $table->dateTime('expense_date');
            $table->text('description')->nullable();
            $table->string('receipt', 255)->nullable();
            $table->timestamps();
            // Indexes
            $table->index('expense_date', 'idx_expenses_date');
            $table->index('category', 'idx_expenses_category');
            $table->index('payment_method', 'idx_expenses_payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
