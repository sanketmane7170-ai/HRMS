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
        // Only add column if it doesn't exist
        if (!Schema::hasColumn('recruitment_applications', 'current_stage')) {
            Schema::table('recruitment_applications', function (Blueprint $table) {
                $table->string('current_stage')->nullable()->after('stage')
                      ->comment('Current stage for employee view synchronization');
            });

            // Copy existing stage values to current_stage
            \DB::statement('UPDATE recruitment_applications SET current_stage = stage WHERE current_stage IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('recruitment_applications', 'current_stage')) {
            Schema::table('recruitment_applications', function (Blueprint $table) {
                $table->dropColumn('current_stage');
            });
        }
    }
};
