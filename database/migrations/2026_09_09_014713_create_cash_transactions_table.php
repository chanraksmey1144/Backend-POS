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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('cash_register_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('transaction_type', 10); // 'cash_in' | 'cash_out'
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Indexes
            $table->index('session_id', 'idx_cash_tx_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
