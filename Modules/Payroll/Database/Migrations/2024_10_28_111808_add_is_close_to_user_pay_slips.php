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
        Schema::table('user_pay_slips', function (Blueprint $table) {
            $table->integer('is_close')->default(0)->after('settlement_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_pay_slips', function (Blueprint $table) {
            $table->dropColumn('is_close');
        });
    }
};
