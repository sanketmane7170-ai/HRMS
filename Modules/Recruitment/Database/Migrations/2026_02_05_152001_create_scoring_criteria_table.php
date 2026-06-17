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
        if (Schema::hasTable('scoring_criteria')) {
            return;
        }
        Schema::create('scoring_criteria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidate_score_id');
            $table->string('criterion_id')->nullable();
            $table->string('criterion_name');
            $table->integer('score');
            $table->decimal('weight', 5, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('candidate_score_id')->references('id')->on('recruitment_candidate_scores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_criteria');
    }
};
