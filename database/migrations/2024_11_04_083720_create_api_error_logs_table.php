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
        Schema::create('api_error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_name')->nullable(); // Name of the API endpoint that triggered the error
            $table->json('response')->nullable();    // JSON structure for the response data or error details
            $table->string('status')->nullable();    // Status of the response (e.g., success, error, etc.)
            $table->integer('user_id')->nullable();    // Status of the response (e.g., success, error, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_error_logs');
    }
};
