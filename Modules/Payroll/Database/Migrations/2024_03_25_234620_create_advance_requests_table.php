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
        Schema::create('advance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 8)->unique();
            $table->enum('type', ['Salary Advance', 'Loan']);
            $table->text('reason');
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('instalments');
            $table->date('start_month');
            $table->enum('status', ['hold', 'pending', 'approved', 'rejected', 'closed', 'cancelled'])->default('pending');
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->unsignedInteger('loan_months')->nullable();
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->unsignedInteger('installments_paid')->default(0);
            $table->unsignedInteger('installments_pending')->nullable();
            $table->dateTime('last_installment_date')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_requests');
    }
};
