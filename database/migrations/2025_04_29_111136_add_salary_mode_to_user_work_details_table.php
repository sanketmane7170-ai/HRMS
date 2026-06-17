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
            $table->string('salary_mode')->nullable()->default('account')->after('is_rider');
        });
    }

    public function down()
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn('salary_mode');
        });
    }

};
