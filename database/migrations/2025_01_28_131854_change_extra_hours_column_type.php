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
        Schema::table('extra_work_requests', function (Blueprint $table) {
            $table->float('extra_hours', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extra_work_requests', function (Blueprint $table) {
            $table->bigInteger('extra_hours')->nullable()->change();
        });
    }
};
