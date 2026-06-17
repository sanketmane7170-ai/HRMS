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
        Schema::table('recruitment_interviews', function (Blueprint $table) {
            // Rename invalid column if exists
            if (Schema::hasColumn('recruitment_interviews', 'schedule_at')) {
                $table->renameColumn('schedule_at', 'scheduled_at');
            } else if (!Schema::hasColumn('recruitment_interviews', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable();
            }

            if (Schema::hasColumn('recruitment_interviews', 'link')) {
                $table->renameColumn('link', 'meeting_link');
            } else if (!Schema::hasColumn('recruitment_interviews', 'meeting_link')) {
                $table->string('meeting_link')->nullable();
            }

            // Add missing columns as per Interview Model - Sanket
            if (!Schema::hasColumn('recruitment_interviews', 'round')) {
                $table->integer('round')->default(1)->after('application_id');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'scheduled_by')) {
                $table->unsignedBigInteger('scheduled_by')->nullable()->after('interviewer_id');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(60)->after('scheduled_at');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'type')) {
                $table->string('type')->default('in-person')->after('duration_minutes');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'status')) {
                $table->string('status')->default('scheduled')->after('type');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'agenda')) {
                $table->text('agenda')->nullable()->after('meeting_link');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'preparation_notes')) {
                $table->text('preparation_notes')->nullable()->after('agenda');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'additional_interviewers')) {
                $table->json('additional_interviewers')->nullable()->after('preparation_notes');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'send_reminder')) {
                $table->boolean('send_reminder')->default(false)->after('additional_interviewers');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'reminder_minutes')) {
                $table->integer('reminder_minutes')->default(30)->after('send_reminder');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('reminder_minutes');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('reminder_sent_at');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'overall_rating')) {
                $table->integer('overall_rating')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'score')) {
                $table->float('score')->nullable()->after('overall_rating');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'calendar_event_ids')) {
                $table->json('calendar_event_ids')->nullable()->after('score');
            }
            if (!Schema::hasColumn('recruitment_interviews', 'notes')) {
                $table->text('notes')->nullable()->after('calendar_event_ids');
            }

            // Remove legacy feedback/result if they've moved to separate tables
            // $table->dropColumn(['feedback', 'result']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_interviews', function (Blueprint $table) {
            // Revert changes if needed
        });
    }
};
