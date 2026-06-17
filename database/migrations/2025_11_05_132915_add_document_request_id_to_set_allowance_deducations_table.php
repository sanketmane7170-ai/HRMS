<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_salary_allowances', function (Blueprint $table) {
            $table->unsignedBigInteger('document_request_id')->nullable()->after('id');

            // optional FK
            // $table->foreign('document_request_id')
            //     ->references('id')
            //     ->on('document_requests')
            //     ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_salary_allowances', function (Blueprint $table) {
            // if FK exists uncomment this
            // $table->dropForeign(['document_request_id']);

            $table->dropColumn('document_request_id');
        });
    }
};
