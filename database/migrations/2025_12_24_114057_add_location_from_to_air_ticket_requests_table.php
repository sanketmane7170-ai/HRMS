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
        Schema::table('air_ticket_requests', function (Blueprint $table) {
            $table->string('location_from')->nullable()->after('return_date');
            $table->string('location_to')->nullable()->after('location_from');
            $table->string('request_type')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_ticket_requests', function (Blueprint $table) {
            //
        });
    }
};
