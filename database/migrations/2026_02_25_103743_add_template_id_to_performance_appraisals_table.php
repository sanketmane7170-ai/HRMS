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
    Schema::table('performance_appraisals', function (Blueprint $table) {
        $table->unsignedBigInteger('template_id')->nullable()->after('employee_id');

        
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_appraisals', function (Blueprint $table) {
            $table->dropColumn('template_id');
        });
    }
};
