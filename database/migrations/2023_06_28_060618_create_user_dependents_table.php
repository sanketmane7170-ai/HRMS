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
        Schema::create('user_dependents', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('nationality');
            $table->string('contact');
            $table->date('date_of_birth');
            $table->string('address')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->enum('relation', ['father', 'mother', 'husband', 'wife', 'son', 'daughter', 'other']);
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dependents');
    }
};
