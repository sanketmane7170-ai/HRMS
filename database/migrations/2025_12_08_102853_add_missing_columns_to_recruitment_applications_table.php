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
        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('recruitment_applications', 'candidate_email')) {
                $table->string('candidate_email')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('recruitment_applications', 'candidate_name')) {
                $table->string('candidate_name')->nullable()->after('candidate_email');
            }
            if (!Schema::hasColumn('recruitment_applications', 'candidate_phone')) {
                $table->string('candidate_phone')->nullable()->after('candidate_name');
            }
            if (!Schema::hasColumn('recruitment_applications', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('candidate_phone');
            }
            if (!Schema::hasColumn('recruitment_applications', 'score')) {
                $table->decimal('score', 5, 2)->nullable()->after('stage');
            }
            if (!Schema::hasColumn('recruitment_applications', 'cover_letter')) {
                $table->text('cover_letter')->nullable()->after('linkedin_url');
            }
            if (!Schema::hasColumn('recruitment_applications', 'resume_path')) {
                $table->string('resume_path')->nullable()->after('resume_url');
            }
            if (!Schema::hasColumn('recruitment_applications', 'expected_salary')) {
                $table->decimal('expected_salary', 10, 2)->nullable()->after('cover_letter');
            }
            if (!Schema::hasColumn('recruitment_applications', 'availability_date')) {
                $table->date('availability_date')->nullable()->after('expected_salary');
            }
        });
        
        // Make user_id nullable (separate schema operation to avoid conflicts)
        if (Schema::hasColumn('recruitment_applications', 'user_id')) {
            try {
                Schema::table('recruitment_applications', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                });
            } catch (\Exception $e) {
                // If changing user_id fails due to foreign key constraints, continue
                \Log::warning('Could not make user_id nullable: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_applications', function (Blueprint $table) {
            // Drop columns only if they exist
            $columnsToCheck = [
                'candidate_email', 'candidate_name', 'candidate_phone',
                'linkedin_url', 'score', 'cover_letter', 'resume_path',
                'expected_salary', 'availability_date'
            ];
            
            $columnsToDelete = [];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('recruitment_applications', $column)) {
                    $columnsToDelete[] = $column;
                }
            }
            
            if (!empty($columnsToDelete)) {
                $table->dropColumn($columnsToDelete);
            }
        });
        
        // Make user_id required again (if safe to do so)
        try {
            Schema::table('recruitment_applications', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        } catch (\Exception $e) {
            \Log::warning('Could not make user_id required: ' . $e->getMessage());
        }
    }
};
