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
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->integer('air_ticket_count')->default(0)->after('air_ticket_setting_id');
            $table->string('renewal_air_ticket')->nullable()->after('air_ticket_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn(['air_ticket_count', 'renewal_air_ticket']);
        });
    }
};
