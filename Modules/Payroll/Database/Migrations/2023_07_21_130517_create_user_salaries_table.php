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
        Schema::create('user_salaries', function (Blueprint $table) {
            $table->id();
            $table->float('basic');
            $table->float('hra')->default(0);
            $table->float('food_allowance')->default(0);
            $table->float('travel_allowance')->default(0);
            $table->float('other_allowance')->default(0);
            $table->float('gross');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('user_id');
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
        Schema::dropIfExists('user_salaries');
    }
};
