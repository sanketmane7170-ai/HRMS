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
        Schema::table('advance_request_histories', function (Blueprint $table) {
            $table->bigInteger('approved_amount')->after('amount')->default(0);
            $table->bigInteger('installments_paid')->after('approved_amount')->default(0);
            $table->bigInteger('installments_pending')->after('installments_paid')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_request_histories', function (Blueprint $table) {
            //
        });
    }
};
