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
        if (Schema::hasTable('compliance_records')) {
            return;
        }
        Schema::create('compliance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ohc_status')->nullable();
            $table->date('ohc_expiry_date')->nullable();
            $table->string('ohc_file')->nullable();
            $table->string('food_safety_training_status')->nullable();
            $table->date('training_completion_date')->nullable();
            $table->string('certificate_file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_records');
    }
};
