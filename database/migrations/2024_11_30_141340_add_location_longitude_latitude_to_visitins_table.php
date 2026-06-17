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
        Schema::table('visitins', function (Blueprint $table) {
            $table->string('location')->nullable()->after('location_id');
            $table->string('longitude')->nullable()->after('location');
            $table->string('latitude')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitins', function (Blueprint $table) {
            $table->dropColumn(['location', 'longitude', 'latitude']);
        });
    }
};
