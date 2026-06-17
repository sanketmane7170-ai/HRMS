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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE recruitment_jobs MODIFY COLUMN status ENUM('draft', 'active', 'paused', 'closed', 'on-hold') DEFAULT 'draft'");

        Schema::table('recruitment_jobs', function (Blueprint $table) {
            // Employment details
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time')->after('role_id');
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'executive'])->nullable()->after('job_type');
            $table->string('location')->nullable()->after('experience_level');
            
            // Compensation
            $table->decimal('min_salary', 10, 2)->nullable()->after('location');
            $table->decimal('max_salary', 10, 2)->nullable()->after('min_salary');
            
            // Work arrangement
            $table->boolean('remote_work')->default(false)->after('max_salary');
            
            // Additional job details
            $table->text('benefits')->nullable()->after('requirements');
            $table->integer('positions_available')->default(1)->after('benefits');
            $table->date('application_deadline')->nullable()->after('positions_available');
            $table->boolean('is_featured')->default(false)->after('application_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'job_type',
                'experience_level', 
                'location',
                'min_salary',
                'max_salary',
                'remote_work',
                'benefits',
                'positions_available',
                'application_deadline',
                'is_featured'
            ]);
            
            // Revert status enum back to original
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE recruitment_jobs MODIFY COLUMN status ENUM('active', 'closed', 'on-hold') DEFAULT 'active'");
        });
    }
};
