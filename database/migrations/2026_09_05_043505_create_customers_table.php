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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')
                  ->nullable()
                  ->constrained('customer_groups')
                  ->nullOnDelete();
            $table->string('name')->index('idx_customers_name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->unsignedInteger('loyalty_points')->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->decimal('outstanding', 14, 2)->default(0);
            $table->string('status', 20)->default('active')->index('idx_customers_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
