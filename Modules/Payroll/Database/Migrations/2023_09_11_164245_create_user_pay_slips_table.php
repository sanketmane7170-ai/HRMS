<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_pay_slips', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->date('slip_generation_date');
            $table->integer('month_code');
            $table->integer('year');
            $table->float('basic')->comment('basic salary will be updated when payslip status paid')->nullable(true);
            $table->float('total_net_salary')->comment('total net salary will be updated when payslip status paid')->nullable(true);
            $table->enum('status', ['paid', 'unpaid'])->default('unpaid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_pay_slips');
    }
};
