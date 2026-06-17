<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Added by Sanket to fix schema mismatch
     */
    public function up(): void
    {
        if (!Schema::hasColumn('probation_reviews', 'option_to_extend_duration_months')) {
            Schema::table('probation_reviews', function (Blueprint $table) {
                $table->integer('option_to_extend_duration_months')->nullable()->after('recommendation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('probation_reviews', 'option_to_extend_duration_months')) {
            Schema::table('probation_reviews', function (Blueprint $table) {
                $table->dropColumn('option_to_extend_duration_months');
            });
        }
    }
};
