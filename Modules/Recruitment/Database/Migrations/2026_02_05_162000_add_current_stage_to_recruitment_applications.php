<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('recruitment_applications') && !Schema::hasColumn('recruitment_applications', 'current_stage')) {
            Schema::table('recruitment_applications', function (Blueprint $table) {
                $table->string('current_stage')->nullable()->after('stage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('recruitment_applications') && Schema::hasColumn('recruitment_applications', 'current_stage')) {
            Schema::table('recruitment_applications', function (Blueprint $table) {
                $table->dropColumn('current_stage');
            });
        }
    }
};
