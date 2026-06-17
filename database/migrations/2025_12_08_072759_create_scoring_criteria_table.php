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
        if (! Schema::hasTable('scoring_criteria')) {
            Schema::create('scoring_criteria', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('candidate_score_id');
                $table->string('criterion_id', 100);
                $table->string('criterion_name');
                $table->tinyInteger('score');    // 1-10 scale
                $table->decimal('weight', 3, 2); // 0.1-1.0 weight
                $table->text('notes')->nullable();
                $table->timestamps();

                // Removed foreign key constraint

                $table->index(['candidate_score_id', 'criterion_id']);
                $table->index(['criterion_name', 'score']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_criteria');
    }
};
