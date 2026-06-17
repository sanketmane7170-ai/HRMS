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
        Schema::create('employee_kpi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_kpi_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('key_performance_indicator_id')->constrained()->onDelete('cascade');
            $table->integer('self_score')->nullable();
            $table->text('self_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpi_items');
    }
};
