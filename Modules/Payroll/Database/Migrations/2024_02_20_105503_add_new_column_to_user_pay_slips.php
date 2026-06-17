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
            $table->string('settlement_status')->after('status'); // Adjust the position as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_pay_slips', function (Blueprint $table) {
            $table->dropColumn('settlement_status');
        });
    }
};
