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
        Schema::create('user_promotion_letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('letter_type_id');
            $table->date('date')->nullable();
            $table->string('new_position')->nullable();
            $table->integer('user_basic_salary')->nullable();
            $table->integer('user_transportation_allowances')->nullable();
            $table->integer('user_housing_allowances')->nullable();
            $table->integer('user_other_allowances')->nullable();
            $table->integer('user_gross_salary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_promotion_letters');
    }
};
