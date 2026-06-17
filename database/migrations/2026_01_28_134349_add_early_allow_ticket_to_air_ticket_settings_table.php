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
        Schema::table('air_ticket_settings', function (Blueprint $table) {
            $table->boolean('early_allow_ticket')->nullable()->after('allow_ticket_booking');
            $table->integer('early_month')->nullable()->after('early_allow_ticket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_ticket_settings', function (Blueprint $table) {
            //
        });
    }
};
