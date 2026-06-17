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
        Schema::create('recruitment_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->decimal('salary', 12, 2)->nullable();
            $table->date('joining_date')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->string('offer_letter_url')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->timestamp('offer_date');
            $table->timestamp('response_deadline')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('application_id')->references('id')->on('recruitment_applications')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['application_id', 'status']);
            $table->index('offer_date');
            $table->index('response_deadline');
        });
    }    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_offers');
    }
};
