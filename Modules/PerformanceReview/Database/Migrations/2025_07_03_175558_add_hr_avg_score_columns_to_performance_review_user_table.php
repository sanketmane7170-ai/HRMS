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
            $table->decimal('reviewer_avg_score', 8, 2)->nullable()->after('hr_review_date');
            $table->decimal('hr_avg_score', 8, 2)->nullable()->after('reviewer_avg_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('performance_review_user', function (Blueprint $table) {
            $table->dropColumn(['reviewer_avg_score', 'hr_avg_score']);
        });
    }
};
