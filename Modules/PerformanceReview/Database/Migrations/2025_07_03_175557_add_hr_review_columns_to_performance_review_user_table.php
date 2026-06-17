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
            $table->string('hr_review')->default('Pending')->after('updated_at');
            $table->timestamp('hr_review_date')->nullable()->after('hr_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('performance_review_user', function (Blueprint $table) {
            $table->dropColumn(['hr_review', 'hr_review_date']);
        });
    }
};
