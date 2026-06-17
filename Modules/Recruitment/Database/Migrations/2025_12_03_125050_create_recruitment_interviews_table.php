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
        Schema::create('recruitment_interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('interviewer_id');
            $table->timestamp('schedule_at');
            $table->string('location')->nullable();
            $table->string('link')->nullable(); // For virtual interviews
            $table->text('feedback')->nullable();
            $table->enum('result', ['pass', 'fail', 'on-hold'])->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
            $table->foreign('interviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['application_id', 'schedule_at']);
            $table->index('interviewer_id');
            $table->index('schedule_at');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_interviews');
    }
};
