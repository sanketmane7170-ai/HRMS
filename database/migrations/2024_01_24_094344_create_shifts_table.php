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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['SS', 'MS']);
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::table('shift_schedules', function (Blueprint $add) {
            $add->integer('shift_id')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');

        Schema::table('shift_schedules', function (Blueprint $drop) {
            $drop->dropColumn('shift_id');
        });
    }
};
