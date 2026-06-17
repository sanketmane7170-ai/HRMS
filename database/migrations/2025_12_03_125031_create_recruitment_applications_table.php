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
        Schema::create('recruitment_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('user_id');
            $table->string('resume_url')->nullable();
            $table->enum('stage', [
                'applied', 'screening', 'shortlisted', 
                'interview', 'offer', 'hired', 'rejected'
            ])->default('applied');
            $table->timestamp('applied_on');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('job_id')->references('id')->on('recruitment_jobs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint to prevent duplicate applications
            $table->unique(['job_id', 'user_id']);

            // Indexes for better performance
            $table->index(['stage', 'applied_on']);
            $table->index('job_id');
            $table->index('user_id');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_applications');
    }
};
