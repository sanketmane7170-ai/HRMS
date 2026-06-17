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
        Schema::table('user_promotion_letters', function (Blueprint $table) {
            $table->decimal('old_salary', 15, 2)->nullable()->after('new_position');
        });
    }
    public function down()
    {
        Schema::table('user_promotion_letters', function (Blueprint $table) {
            $table->dropColumn('old_salary');
        });
    }
};
