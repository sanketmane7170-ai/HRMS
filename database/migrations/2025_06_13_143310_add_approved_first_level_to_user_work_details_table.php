<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->boolean('approved_first_level')->default(false)->after('report_to_ids');
        });
    }

    public function down()
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn('approved_first_level');
        });
    }
};
