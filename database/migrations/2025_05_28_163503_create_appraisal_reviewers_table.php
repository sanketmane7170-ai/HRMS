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
        Schema::create('appraisal_reviewers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appraisal_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->integer('level');
            $table->enum('status', ['pending', 'reviewed', 'skipped'])->default('pending');
            $table->timestamps();

            $table->foreign('appraisal_id')->references('id')->on('performance_appraisals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appraisal_reviewers');
    }
};
