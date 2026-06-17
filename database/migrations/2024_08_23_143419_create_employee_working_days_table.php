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
        Schema::create('employee_working_days', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('month_code')->comment("1 means january and 12 means december")->nullable(true);
            $table->integer('year')->comment("check in which year we want allowance details")->nullable(true);
            $table->integer('total_working_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_working_days');
    }
};
