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
        Schema::create('user_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to the users table
            $table->string('emergency_name')->nullable();;
            $table->string('emergency_relation')->nullable();; // Relation to the user, e.g., Parent, Friend
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_isd_code')->nullable(); // Default ISD code (e.g., US)
            $table->string('emergency_email')->nullable(); // Optional
            $table->string('emergency_home_country')->nullable(); // Optional
            $table->string('emergency_home_address')->nullable(); // Optional
            $table->string('emergency_local_country')->nullable(); // Optional
            $table->string('emergency_local_address')->nullable(); // Optional
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_emergency_contacts');
    }
};
