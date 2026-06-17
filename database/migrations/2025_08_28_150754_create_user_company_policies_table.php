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
        Schema::create('user_company_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_policy_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('ack_status')->default(false);
            $table->dateTime('ack_datetime')->nullable();
            $table->string('ack_document')->nullable();
            $table->timestamps();

            // Foreign Keys (optional if you have related tables)
            $table->foreign('company_policy_id')->references('id')->on('company_policies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_company_policies');
    }
};
