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
        Schema::create('visitins', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time');
            $table->enum('type', ['start', 'end']);
            $table->integer('location_id');
            $table->foreignId('user_id')->constrained();
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->time('visit_in')->after('break_in')->nullable();
            $table->time('visit_out')->after('break_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visitins');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('visit_in');
            $table->dropColumn('visit_out');
        });
    }
};
