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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->decimal('amount');
            $table->string('payment_mode');
            $table->string('remark');
            $table->unsignedBigInteger('expense_type_id'); // Correct type
            $table->foreign('expense_type_id')->references('id')->on('expense_types');
            $table->string('document')->nullable();
            $table->string('status')->default("pending");
            $table->unsignedBigInteger('user_id'); // Correct type
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
