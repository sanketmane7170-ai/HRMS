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
        Schema::create('key_performance_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duration_id')
                ->constrained('review_durations') 
                ->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_performance_indicators');
    }
};
