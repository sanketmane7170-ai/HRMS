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
        Schema::table('user_warnings', function (Blueprint $table) {
            $table->after('user_id', function ($table) {
            $table->string('acknowledgement')->nullable()->default(null);
            $table->string('document')->nullable()->default(null);
            $table->string('ack_status')->nullable()->default(null);
            $table->date('ack_datetime')->nullable()->default(null);
            $table->string('ack_document')->nullable()->default(null);
          });
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
