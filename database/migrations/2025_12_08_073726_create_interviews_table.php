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
        if (! Schema::hasTable('interviews')) {
            Schema::create('interviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->unsignedBigInteger('interviewer_id');
                $table->unsignedBigInteger('scheduled_by');
                $table->datetime('scheduled_at');
                $table->integer('duration_minutes')->default(60);
                $table->enum('type', ['phone', 'video', 'in-person', 'panel'])->default('video');
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'rescheduled', 'no_show'])->default('scheduled');
                $table->string('location')->nullable();
                $table->string('meeting_link')->nullable();
                $table->text('agenda')->nullable();
                $table->text('preparation_notes')->nullable();
                $table->json('additional_interviewers')->nullable(); // For panel interviews
                $table->boolean('send_reminder')->default(true);
                $table->integer('reminder_minutes')->default(60);
                $table->timestamp('reminder_sent_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->datetime('completed_at')->nullable();
                $table->json('calendar_event_ids')->nullable(); // Store external calendar event IDs
                $table->timestamps();

                $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
                $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('restrict');
                $table->foreign('scheduled_by')->references('id')->on('users')->onDelete('restrict');

                $table->index(['application_id', 'scheduled_at']);
                $table->index(['interviewer_id', 'scheduled_at']);
                $table->index(['status', 'scheduled_at']);
                $table->index(['scheduled_at', 'send_reminder']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
