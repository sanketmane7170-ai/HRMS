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
        if (Schema::hasTable('recruitment_candidate_scores')) {
            return;
        }
        Schema::create('recruitment_candidate_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('scored_by');
            $table->decimal('overall_score', 8, 2);
            $table->string('scoring_method')->nullable();
            $table->text('interviewer_notes')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->string('recommendation');
            $table->text('recommendation_notes')->nullable();
            $table->integer('cultural_fit_score')->nullable();
            $table->integer('technical_skills_score')->nullable();
            $table->integer('communication_score')->nullable();
            $table->integer('leadership_potential_score')->nullable();
            $table->integer('problem_solving_score')->nullable();
            $table->decimal('average_component_score', 8, 2)->nullable();
            $table->decimal('recommendation_weight', 5, 2)->nullable();
            $table->boolean('is_final_score')->default(false);
            $table->text('next_steps')->nullable();
            $table->integer('interview_round')->default(1);
            $table->string('interview_type')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
            $table->foreign('scored_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidate_scores');
    }
};
