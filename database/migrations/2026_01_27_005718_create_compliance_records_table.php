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
        Schema::create('compliance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Phase 3.1: Occupational Health Card (OHC)
            $table->string('ohc_status')->default('pending'); // pending, applied, issued
            $table->date('ohc_expiry_date')->nullable();
            $table->string('ohc_file')->nullable();
            
            // Phase 3.2: Food Safety Training
            $table->string('food_safety_training_status')->default('pending'); // pending, assigned, passed
            $table->date('training_completion_date')->nullable();
            $table->string('certificate_file')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
