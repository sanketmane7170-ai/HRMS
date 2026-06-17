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
        Schema::table('recruitment_jobs', function (Blueprint $table) {
            // Add responsibilities field after requirements
            $table->text('responsibilities')->nullable()->after('requirements');
            
            // Add skills field as JSON to store array of skills
            $table->json('skills')->nullable()->after('responsibilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->dropColumn(['responsibilities', 'skills']);
        });
    }
};