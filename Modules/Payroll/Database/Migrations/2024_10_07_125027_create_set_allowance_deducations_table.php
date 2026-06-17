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
        Schema::create('set_allowance_deducations', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->comment('1=allowance,2=deducations')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_allowance_deducations');
    }
};
