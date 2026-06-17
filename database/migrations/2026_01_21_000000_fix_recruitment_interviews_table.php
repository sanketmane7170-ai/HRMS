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
        Schema::dropIfExists('recruitment_interviews');

        Schema::create('recruitment_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('interviewer_id');
            $table->unsignedBigInteger('scheduled_by')->nullable();
            
            // Core scheduling fields matching Interview Model casts & usage
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes')->default(60);
            $table->string('type'); // phone, video, in_person
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, etc
            
            // Details
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable(); // Model uses 'meeting_link'
            $table->text('agenda')->nullable();
            $table->text('preparation_notes')->nullable();
            $table->json('additional_interviewers')->nullable();
            
            // Reminders
            $table->boolean('send_reminder')->default(false);
            $table->integer('reminder_minutes')->default(30);
            $table->dateTime('reminder_sent_at')->nullable();
            
            // Cancellation & Completion
            $table->text('cancellation_reason')->nullable();
            $table->dateTime('completed_at')->nullable();
            
            // Scoring
            $table->decimal('overall_rating', 3, 1)->nullable(); // 0-10 scale usually
            $table->decimal('score', 5, 2)->nullable(); // 0-100 scale
            
            // Calendar Integration
            $table->json('calendar_event_ids')->nullable();
            
            // Process tracking
            $table->integer('round')->default(1);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Keep for compatibility if needed
            
            $table->timestamps();

            // Foreign Keys
            $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
            $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('scheduled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_interviews');
    }
};
