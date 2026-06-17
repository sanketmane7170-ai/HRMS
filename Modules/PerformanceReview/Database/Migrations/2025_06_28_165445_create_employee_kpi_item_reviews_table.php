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
        Schema::create('employee_kpi_item_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_kpi_item_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('step_number');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('reviewer_score');
            $table->text('reviewer_remarks')->nullable();
            $table->timestamps();

            $table->unique(['employee_kpi_item_id', 'step_number'], 'kpi_item_step_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpi_item_reviews');
    }
};
