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
        Schema::create('air_ticket_settings', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name');
            $table->string('allowance_currency');
            $table->decimal('allowance_amount', 10, 2);
            $table->integer('request_after_months');
            $table->string('request_after_months_date');
            $table->integer('policy_renewal_months');
            $table->integer('request_limit_per_cycle');
            $table->boolean('allow_reimbursement')->default(false);
            $table->boolean('allow_encashment')->default(false);
            $table->boolean('allow_ticket_booking')->default(false);
            $table->decimal('encashment_amount', 10, 2)->nullable();
            $table->string('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('air_ticket_settings');
    }
};
