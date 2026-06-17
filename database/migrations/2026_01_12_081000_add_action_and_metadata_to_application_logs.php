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
        Schema::table('recruitment_application_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment_application_logs', 'action')) {
                $table->string('action')->nullable()->after('new_stage');
            }
            if (!Schema::hasColumn('recruitment_application_logs', 'metadata')) {
                $table->json('metadata')->nullable()->after('description');
            }
            // Ensure new_stage is nullable for non-stage-change actions
            $table->string('new_stage')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_application_logs', function (Blueprint $table) {
            $table->dropColumn(['action', 'metadata']);
            $table->string('new_stage')->nullable(false)->change();
        });
    }
};
