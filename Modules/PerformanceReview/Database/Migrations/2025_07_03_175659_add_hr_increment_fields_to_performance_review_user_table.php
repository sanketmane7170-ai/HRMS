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
        Schema::table('performance_review_user', function (Blueprint $table) {
            $table->decimal('hr_increment_percent', 5, 2)->nullable();
            $table->decimal('hr_basic_percent', 5, 2)->nullable();
            $table->decimal('hr_housing_percent', 5, 2)->nullable();
            $table->decimal('hr_transport_percent', 5, 2)->nullable();
            $table->decimal('hr_other_percent', 5, 2)->nullable();
            $table->decimal('hr_incentive_percent', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('performance_review_user', function (Blueprint $table) {
            $table->dropColumn([
                'hr_increment_percent',
                'hr_basic_percent',
                'hr_housing_percent',
                'hr_transport_percent',
                'hr_other_percent',
                'hr_incentive_percent'
            ]);
        });
    }
};
