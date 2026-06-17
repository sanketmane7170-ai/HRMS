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
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->string('total_working_days')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_salaries', function (Blueprint $table) {
            $table->string('total_working_days')->default(30)->change();
        });
    }
};
