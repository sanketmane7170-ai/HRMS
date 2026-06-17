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
        Schema::create('leave_approval_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('step_number');
            $table->json('approvers'); // Store approvers as JSON
            $table->integer('level')->nullable(); // Add level column as nullable
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_approval_settings');
    }
};
