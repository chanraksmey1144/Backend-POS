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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index('idx_suppliers_name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->string('address')->nullable();
            $table->decimal('total_purchases', 14, 2)->default(0);
            $table->decimal('outstanding', 14, 2)->default(0);
            $table->string('status', 20)->default('active')->index('idx_suppliers_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
