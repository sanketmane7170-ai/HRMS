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
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('score_criteria_id')->nullable()->after('question_set_id');

            $table->foreign('score_criteria_id')
                ->references('id')
                ->on('score_criteria')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropForeign(['score_criteria_id']);
            $table->dropColumn('score_criteria_id');
        });
    }
};
