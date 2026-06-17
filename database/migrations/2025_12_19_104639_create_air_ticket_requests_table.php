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
        Schema::create('air_ticket_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->date('journey_date')->nullable();
            $table->date('return_date')->nullable();
            $table->decimal('requested_amount', 10, 2)->nullable();
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->string('ticket_proof')->nullable();
            $table->string('status')->default('pending')->comment('pending, approved, rejected');
            $table->string('payment_mode')->nullable();
            $table->text('admin_remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_ticket_requests');
    }
};
