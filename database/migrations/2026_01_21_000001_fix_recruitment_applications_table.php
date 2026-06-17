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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('recruitment_applications');
        Schema::enableForeignKeyConstraints();
        
        Schema::create('recruitment_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Can be nullable for external candidates
            
            // Candidate Details (for external or manual entry)
            $table->string('candidate_name')->nullable();
            $table->string('candidate_email')->nullable();
            $table->string('candidate_phone')->nullable();
            
            // Professional Details
            $table->string('resume_path')->nullable();
            $table->string('resume_url')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            
            // Application Details
            $table->decimal('current_salary', 15, 2)->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->integer('notice_period')->nullable()->comment('in days');
            $table->date('availability_date')->nullable();
            $table->integer('years_experience')->default(0);
            
            // Status & Stage
            $table->string('stage')->default('applied'); 
            // applied, screening, shortlisted, interview, offer, hired, rejected
            
            $table->decimal('score', 5, 2)->nullable();
            $table->dateTime('applied_on')->useCurrent();
            $table->text('notes')->nullable();
            
            // Screening Questions
            $table->boolean('willing_to_relocate')->default(false);
            $table->boolean('authorization_to_work')->default(true);
            $table->string('current_company')->nullable();
            $table->string('current_position')->nullable();
            
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('job_id')->references('id')->on('recruitment_jobs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['stage', 'applied_on']);
            $table->index('job_id');
            $table->index('candidate_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_applications');
    }
};
