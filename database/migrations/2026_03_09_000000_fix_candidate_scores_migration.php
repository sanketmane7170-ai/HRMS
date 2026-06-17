<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Sanket: This migration fixes the production issue where `candidate_scores`
// table already exists but the original migration was never recorded.
// Running this will safely skip re-creation and register the old migration.

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If the table already exists, just register the old migration as run
        // so Laravel stops trying to re-create it on the next `php artisan migrate`.
        if (Schema::hasTable('candidate_scores')) {
            $alreadyTracked = DB::table('migrations')
                ->where('migration', '2025_12_08_072749_create_candidate_scores_table')
                ->exists();

            if (!$alreadyTracked) {
                DB::table('migrations')->insert([
                    'migration' => '2025_12_08_072749_create_candidate_scores_table',
                    'batch'     => DB::table('migrations')->max('batch') ?? 1,
                ]);
            }

            return;
        }

        // If the table does NOT exist (fresh environment), create it now.
        Schema::create('candidate_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('scored_by');
            $table->decimal('overall_score', 5, 2);
            $table->enum('scoring_method', ['weighted_average', 'simple_average', 'custom']);
            $table->text('interviewer_notes')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->enum('recommendation', [
                'strongly_recommend', 'recommend', 'neutral',
                'not_recommend', 'strongly_not_recommend',
            ]);
            $table->text('recommendation_notes')->nullable();
            $table->tinyInteger('cultural_fit_score')->nullable();
            $table->tinyInteger('technical_skills_score')->nullable();
            $table->tinyInteger('communication_score')->nullable();
            $table->tinyInteger('leadership_potential_score')->nullable();
            $table->tinyInteger('problem_solving_score')->nullable();
            $table->decimal('average_component_score', 4, 2)->nullable();
            $table->tinyInteger('recommendation_weight')->default(3);
            $table->boolean('is_final_score')->default(false);
            $table->text('next_steps')->nullable();
            $table->tinyInteger('interview_round');
            $table->enum('interview_type', [
                'phone_screening', 'technical', 'behavioral',
                'panel', 'final', 'cultural_fit',
            ]);
            $table->timestamp('scored_at');
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
        Schema::dropIfExists('candidate_scores');
    }
};
