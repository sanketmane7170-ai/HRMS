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
        Schema::table('checkins', function (Blueprint $table) {
            $table->string('location')->nullable();  // For storing location name
            $table->string('longitude')->nullable(); // For longitude
            $table->string('latitude')->nullable();  // For latitude
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            $table->dropColumn(['location', 'longitude', 'latitude']);
        });
    }
};
