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
        Schema::create('e_m_i_allowance_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emi_id');
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month_amount')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->dateTime('paid_at')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_m_i_allowance_data');
    }
};
