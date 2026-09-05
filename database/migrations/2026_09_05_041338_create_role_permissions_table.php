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
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->cascadeOnDelete();
            $table->string('permission', 100);
            $table->timestamp('created_at')->useCurrent();
            // Composite primary key
            $table->primary(['role_id', 'permission']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
