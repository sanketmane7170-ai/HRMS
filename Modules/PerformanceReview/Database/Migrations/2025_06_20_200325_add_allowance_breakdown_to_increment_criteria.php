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
    Schema::table('increment_criteria', function (Blueprint $table) {
        $table->decimal('basic_percent', 5, 2)->default(0);
        $table->decimal('housing_percent', 5, 2)->default(0);
        $table->decimal('transport_percent', 5, 2)->default(0);
        $table->decimal('other_percent', 5, 2)->default(0);
        $table->decimal('incentive_percent', 5, 2)->virtualAs('basic_percent + housing_percent + transport_percent + other_percent');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
       Schema::table('increment_criteria', function (Blueprint $table) {
        $table->dropColumn([
            'basic_percent',
            'housing_percent',
            'transport_percent',
            'other_percent',
            'incentive_percent',
        ]);
    });
    }
};
