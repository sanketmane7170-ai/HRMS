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
        if (!Schema::hasTable('interviews')) {
            Schema::create('interviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('application_id');
                $table->integer('round')->default(1);
                $table->unsignedBigInteger('interviewer_id');
                $table->unsignedBigInteger('scheduled_by')->nullable();
                $table->timestamp('scheduled_at');
                $table->integer('duration_minutes')->default(30);
                $table->string('type')->default('video'); // video, phone, in-person
                $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, no-show
                $table->string('location')->nullable();
                $table->string('meeting_link')->nullable();
                $table->text('agenda')->nullable();
                $table->text('preparation_notes')->nullable();
                $table->json('additional_interviewers')->nullable();
                $table->boolean('send_reminder')->default(false);
                $table->integer('reminder_minutes')->default(15);
                $table->timestamp('reminder_sent_at')->nullable();
                $table->text('cancellation_reason')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->decimal('overall_rating', 3, 1)->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->json('calendar_event_ids')->nullable();
                
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();

                $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
                $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('scheduled_by')->references('id')->on('users')->onDelete('set null');
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
