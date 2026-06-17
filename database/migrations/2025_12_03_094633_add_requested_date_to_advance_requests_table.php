<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Sanket - Add requested_date field to track when advance/loan requests are submitted by users
     * This complements the existing approved_date field to provide complete request lifecycle tracking
     * requested_date: Auto-filled when user submits request (current system date)
     * approved_date: Auto-filled when admin approves request (existing field)
     */
    public function up(): void
    {
        Schema::table('advance_requests', function (Blueprint $table) {
            // Sanket - Add requested_date column to track when request was originally created
            $table->date('requested_date')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_requests', function (Blueprint $table) {
            $table->dropColumn('requested_date');
        });
    }
};
