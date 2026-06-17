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
        // Candidate Scores Table
        if (!Schema::hasTable('candidate_scores')) {
            Schema::create('candidate_scores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->unsignedBigInteger('scored_by');
                $table->decimal('overall_score', 5, 2)->nullable();
                $table->string('scoring_method')->nullable();
                $table->text('interviewer_notes')->nullable();
                $table->json('strengths')->nullable();
                $table->json('weaknesses')->nullable();
                $table->string('recommendation')->nullable();
                $table->text('recommendation_notes')->nullable();
                
                // Component scores
                $table->decimal('cultural_fit_score', 5, 2)->nullable();
                $table->decimal('technical_skills_score', 5, 2)->nullable();
                $table->decimal('communication_score', 5, 2)->nullable();
                $table->decimal('leadership_potential_score', 5, 2)->nullable();
                $table->decimal('problem_solving_score', 5, 2)->nullable();
                $table->decimal('average_component_score', 5, 2)->nullable();
                
                $table->decimal('recommendation_weight', 5, 2)->nullable();
                $table->boolean('is_final_score')->default(false);
                $table->text('next_steps')->nullable();
                
                $table->integer('interview_round')->nullable();
                $table->string('interview_type')->nullable();
                
                $table->timestamp('scored_at')->nullable();
                
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
                $table->foreign('scored_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Interview Feedback Table
        if (!Schema::hasTable('interview_feedback')) {
            Schema::create('interview_feedback', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('interview_id')->nullable();
                $table->unsignedBigInteger('application_id');
                $table->unsignedBigInteger('interviewer_id');
                $table->timestamp('interview_date')->nullable();
                $table->string('interview_type')->nullable();
                $table->integer('interview_round')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->string('status')->default('scheduled');
                
                $table->text('questions_asked')->nullable();
                $table->text('candidate_responses')->nullable();
                $table->text('interviewer_observations')->nullable();
                $table->text('technical_assessment')->nullable();
                $table->json('skills_demonstrated')->nullable();
                $table->text('concerns_raised')->nullable();
                $table->text('positive_highlights')->nullable();
                
                $table->string('recommendation')->nullable();
                $table->text('recommendation_reason')->nullable();
                $table->decimal('overall_rating', 3, 1)->nullable();
                
                $table->boolean('candidate_showed_up')->default(true);
                $table->boolean('candidate_on_time')->default(true);
                
                $table->text('follow_up_actions')->nullable();
                $table->timestamp('completed_at')->nullable();
                
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
                $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('cascade');
                // We use set null/cascade depending on requirements, usually headers are optional if interview deleted
                // But better to keep feedback even if interview record is manipulated, though application link is strict
            });
        }

        // Scoring Criteria Table
        if (!Schema::hasTable('scoring_criteria')) {
            Schema::create('scoring_criteria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('candidate_score_id');
                $table->string('criterion_id')->nullable();
                $table->string('criterion_name')->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->decimal('weight', 5, 2)->nullable();
                $table->text('notes')->nullable();
                
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('candidate_score_id')->references('id')->on('candidate_scores')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_criteria');
        Schema::dropIfExists('interview_feedback');
        Schema::dropIfExists('candidate_scores');
    }
};
