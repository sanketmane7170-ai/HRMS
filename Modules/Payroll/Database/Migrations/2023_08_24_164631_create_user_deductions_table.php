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
        Schema::create('user_deductions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('deduction_type');
            $table->float('amount')->default(0);
            $table->float('percentage_amount')->default(0);
            $table->integer('salary_id')->default(0);
            $table->integer('user_id');
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
        Schema::dropIfExists('user_deductions');
    }
};
