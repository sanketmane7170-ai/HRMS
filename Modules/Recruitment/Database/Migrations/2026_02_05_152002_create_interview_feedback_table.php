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
        if (Schema::hasTable('interview_feedback')) {
            return;
        }
        Schema::create('interview_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interview_id')->nullable();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('interviewer_id');
            $table->timestamp('interview_date')->nullable();
            $table->string('interview_type')->nullable();
            $table->integer('interview_round')->default(1);
            $table->integer('duration_minutes')->nullable();
            $table->string('status')->default('completed');
            $table->text('questions_asked')->nullable();
            $table->text('candidate_responses')->nullable();
            $table->text('interviewer_observations')->nullable();
            $table->text('technical_assessment')->nullable();
            $table->json('skills_demonstrated')->nullable();
            $table->text('concerns_raised')->nullable();
            $table->text('positive_highlights')->nullable();
            $table->string('recommendation')->nullable();
            $table->text('recommendation_reason')->nullable();
            $table->integer('overall_rating')->nullable();
            $table->boolean('candidate_showed_up')->default(true);
            $table->boolean('candidate_on_time')->default(true);
            $table->text('follow_up_actions')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
            $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('cascade');
            // interview_id foreign key depends on recruitment_interviews table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_feedback');
    }
};
