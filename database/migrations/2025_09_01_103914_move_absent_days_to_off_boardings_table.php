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
        Schema::table('off_boardings', function (Blueprint $table) {
            // Remove from user_settlements
            Schema::table('user_settlements', function (Blueprint $table) {
                $table->dropColumn('absent_days');
            });

            // Add to off_boardings
            Schema::table('off_boardings', function (Blueprint $table) {
                $table->integer('absent_days')->nullable()->after('salary_month_day');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('off_boardings', function (Blueprint $table) {
            // Rollback: remove from off_boardings and add back to user_settlements
            Schema::table('off_boardings', function (Blueprint $table) {
                $table->dropColumn('absent_days');
            });

            Schema::table('user_settlements', function (Blueprint $table) {
                $table->integer('absent_days')->nullable()->after('salary_month_day');
            });
        });
    }
};
