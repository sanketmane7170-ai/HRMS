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
        Schema::table('user_bank_details', function (Blueprint $table) {
            $table->string('routing_number')->after('swift_code')->nullable('true');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_bank_details', function (Blueprint $table) {
            $table->dropColumn('routing_number');
        });
    }
};
