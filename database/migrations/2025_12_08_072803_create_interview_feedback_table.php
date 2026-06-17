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
        if (! Schema::hasTable('interview_feedback')) {
            Schema::create('interview_feedback', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->unsignedBigInteger('interviewer_id');
                $table->datetime('interview_date');
                $table->enum('interview_type', ['phone_screening', 'technical', 'behavioral', 'panel', 'final', 'cultural_fit']);
                $table->tinyInteger('interview_round');
                $table->integer('duration_minutes')->nullable();
                $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled']);
                $table->text('questions_asked')->nullable();
                $table->text('candidate_responses')->nullable();
                $table->text('interviewer_observations')->nullable();
                $table->text('technical_assessment')->nullable();
                $table->json('skills_demonstrated')->nullable();
                $table->text('concerns_raised')->nullable();
                $table->text('positive_highlights')->nullable();
                $table->enum('recommendation', ['hire', 'reject', 'next_round', 'hold']);
                $table->text('recommendation_reason')->nullable();
                $table->tinyInteger('overall_rating'); // 1-10 scale
                $table->boolean('candidate_showed_up')->default(true);
                $table->boolean('candidate_on_time')->default(true);
                $table->text('follow_up_actions')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
                $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('restrict');

                $table->index(['application_id', 'interview_round']);
                $table->index(['interviewer_id', 'interview_date']);
                $table->index(['interview_type', 'status']);
                $table->index(['recommendation', 'overall_rating']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_feedback');
    }
};
