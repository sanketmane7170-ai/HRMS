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
        Schema::create('location_visits', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('location')->nullable(true);
            $table->string('visit_purpose')->nullable(true);
            $table->time('visit_in')->nullable();
            $table->time('visit_out')->nullable();
            $table->integer('status')->default(0);
            $table->foreignId('user_id')->constrained();
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
        Schema::dropIfExists('location_visit');
    }
};
