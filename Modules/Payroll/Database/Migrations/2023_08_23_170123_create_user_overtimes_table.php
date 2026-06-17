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
        Schema::create('user_overtimes', function (Blueprint $table) {
            $table->id();
            $table->string('overtime_type');
            $table->float('rate_per_hour');
            $table->float('hours')->default(0);
            $table->integer('salary_id')->default(0);
            $table->foreignId('user_id');
            $table->float('calculated_amount');
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
        Schema::dropIfExists('user_overtimes');
    }
};
