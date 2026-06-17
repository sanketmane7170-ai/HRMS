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
        Schema::create('user_work_details', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('company_name');
            $table->string('work_week');
            $table->date('joining_date');
            $table->date('probation_end_date');
            $table->foreignId('user_id')->unique()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_work_details');
    }
};
