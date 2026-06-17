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
        Schema::table('user_deductions', function (Blueprint $table) {
            $table->unsignedBigInteger('document_request_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('user_deductions', function (Blueprint $table) {
            $table->dropColumn('document_request_id');
        });
    }
};
