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
        Schema::table('air_ticket_settings', function (Blueprint $table) {
            $table->string('request_after_from')->nullable()->after('request_after_months'); // Replace 'existing_column_name' with the column name after which you want to add this column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('air_ticket_settings', function (Blueprint $table) {
            $table->dropColumn('request_after_from');
        });
    }
};
