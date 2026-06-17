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
        Schema::create('recruitment_application_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->string('previous_stage')->nullable();
            $table->string('new_stage');
            $table->unsignedBigInteger('changed_by');
            $table->text('description')->nullable();
            $table->timestamp('created_at');

            // Foreign key constraints
            $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['application_id', 'created_at']);
            $table->index('changed_by');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_application_logs');
    }
};
