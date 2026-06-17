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
        if (Schema::hasTable('probation_reviews')) {
            return;
        }
        Schema::create('probation_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('reviewer_id')->nullable();
            $table->integer('cycle_number')->default(1);
            $table->date('scheduled_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('pending');
            $table->integer('performance_score')->nullable();
            $table->string('recommendation')->nullable();
            $table->text('manager_comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->text('hr_comments')->nullable();
            $table->integer('option_to_extend_duration_months')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('probation_reviews');
    }
};
