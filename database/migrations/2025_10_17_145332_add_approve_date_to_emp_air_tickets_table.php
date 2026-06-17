<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_m_p_air_tickets', function (Blueprint $table) {
            $table->dateTime('approve_date')->nullable()->after('status'); 
            // use after() to position column, nullable for tickets not yet approved
        });
    }

    public function down(): void
    {
        Schema::table('e_m_p_air_tickets', function (Blueprint $table) {
            $table->dropColumn('approve_date');
        });
    }
};
