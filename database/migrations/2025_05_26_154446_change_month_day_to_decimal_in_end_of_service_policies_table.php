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
        Schema::table('end_of_service_policies', function (Blueprint $table) {
            $table->decimal('month_day', 11, 1)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('end_of_service_policies', function (Blueprint $table) {
            $table->integer('month_day')->nullable()->change();
        });
    }
};
