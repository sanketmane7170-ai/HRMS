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
            $table->boolean('is_rider')->default(false)->after('air_ticket_setting_id'); // Replace 'last_column_name' with the name of the column after which you want to add `is_rider`.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn('is_rider');
        });
    }
};
