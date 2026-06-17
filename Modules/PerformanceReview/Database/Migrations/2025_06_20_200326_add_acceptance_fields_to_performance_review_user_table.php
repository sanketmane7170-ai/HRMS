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
            $table->enum('employee_response', ['Pending', 'Accepted', 'Declined'])->default('Pending');
            $table->timestamp('responded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('performance_review_user', function (Blueprint $table) {
            $table->dropColumn([
                'employee_response',
                'responded_at',
            ]);
        });
    }
};
