<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('performance_appraisals', function (Blueprint $table) {
            $table->date('appraisal_date')->nullable()->after('period');
        });
    }

    public function down()
    {
        Schema::table('performance_appraisals', function (Blueprint $table) {
            $table->dropColumn('appraisal_date');
        });
    }
};
