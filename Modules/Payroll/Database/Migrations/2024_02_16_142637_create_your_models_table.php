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
        Schema::create('payroll_policy', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['Fixed', 'Hourly']);
            $table->decimal('hourly_charges')->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->integer('max_hours_per_month')->nullable();
            $table->string('formula')->nullable();
            $table->decimal('fixed_amount')->nullable(); // New field
            $table->integer('min_hours_per_day')->nullable(); // New field
            $table->integer('min_hours_per_month')->nullable(); // New field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_policy');
    }
};
