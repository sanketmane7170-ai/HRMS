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
        Schema::create('end_of_service_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_type_id')->nullable();
            $table->string('salary_type')->nullable();
            $table->integer('month_day')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('end_of_service_policies');
    }
};
