<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('bulk_operation_logs')) {
            Schema::create('bulk_operation_logs', function (Blueprint $table) {
                $table->id();
                $table->string('action');
                $table->json('application_ids');
                $table->foreignId('user_id')->constrained('users');
                $table->json('metadata')->nullable();
                $table->string('status'); // success, failed, partial
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_operation_logs');
    }
};
