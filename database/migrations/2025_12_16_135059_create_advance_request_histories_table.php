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
        Schema::create('advance_request_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advance_request_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('action_date')->nullable();
            $table->integer('amount')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_request_histories');
    }
};
