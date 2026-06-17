<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('score_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g. Excellent, Good, Average, etc.
            $table->integer('min_score');
            $table->integer('max_score');
            $table->text('description')->nullable(); // Optional
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_criteria');
    }
};
