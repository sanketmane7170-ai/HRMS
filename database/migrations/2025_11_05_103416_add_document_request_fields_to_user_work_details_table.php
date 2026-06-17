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
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->integer('free_document_request')->default(0)->after('user_id');
            $table->decimal('document_request_charge', 10, 2)->default(0)->after('free_document_request');
        });
    }

    public function down(): void
    {
        Schema::table('user_work_details', function (Blueprint $table) {
            $table->dropColumn(['free_document_request', 'document_request_charge']);
        });
    }
};
