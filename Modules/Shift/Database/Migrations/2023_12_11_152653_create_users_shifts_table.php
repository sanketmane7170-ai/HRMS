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
        Schema::create('users_shifts', function (Blueprint $table) {
            $table->id();
            $table->date('assigned_for_date');
            $table->integer('schedule_id');
            $table->integer('user_id');
            $table->integer('assigned_by_id');
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
        Schema::dropIfExists('users_shifts');
    }
};
