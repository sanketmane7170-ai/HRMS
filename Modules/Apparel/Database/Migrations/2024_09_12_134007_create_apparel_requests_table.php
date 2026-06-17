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
        Schema::create('apparel_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->unsignedBigInteger('apparel_id');
            $table->foreign('apparel_id')->references('id')->on('apparels')->onDelete('restrict');
            $table->integer('number_of_apparel')->nullable();
            $table->integer('status')->default('0')->comment('0=>pending,1=>approved,2=>rejected,3=>cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apparel_requests');
    }
};
