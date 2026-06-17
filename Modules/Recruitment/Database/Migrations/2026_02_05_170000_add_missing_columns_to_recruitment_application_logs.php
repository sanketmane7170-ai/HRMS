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
            // Add updated_at if missing
            if (!Schema::hasColumn('recruitment_application_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
            
            // Add created_by if missing
            if (!Schema::hasColumn('recruitment_application_logs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            }
            
            // Add updated_by if missing
            if (!Schema::hasColumn('recruitment_application_logs', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_application_logs', function (Blueprint $table) {
            $table->dropColumn(['updated_at', 'created_by', 'updated_by']);
        });
    }
};
