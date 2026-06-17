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
        Schema::table('operational_readiness', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id')->nullable()->after('induction_completed');
            $table->unsignedBigInteger('apparel_id')->nullable()->after('asset_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operational_readiness', function (Blueprint $table) {
            $table->dropColumn(['asset_id', 'apparel_id']);
        });
    }
};
