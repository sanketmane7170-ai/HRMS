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
        Schema::create('onboarding_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id')->nullable(); 
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed, converted
            $table->integer('progress_percent')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_records');
    }
};
