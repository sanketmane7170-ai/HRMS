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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_duration_id')->constrained();
            $table->foreignId('question_set_id')->nullable()->constrained();
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Declined'])->default('Pending');
            $table->date('start_date')->nullable();
            $table->integer('score')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
