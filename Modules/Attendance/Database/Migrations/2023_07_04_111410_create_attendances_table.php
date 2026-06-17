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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'leave', 'holiday', 'weekend']);
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->double('total_worked')->default(0)->comment('in minutes');
            $table->text('remark')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('created_by_id')->constrained('users');
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
        Schema::dropIfExists('attendances');
    }
};
