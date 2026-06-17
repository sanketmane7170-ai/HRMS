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
        Schema::table('apparel_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['apparel_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
                  
            $table->foreign('apparel_id')
                  ->references('id')
                  ->on('apparels')
                  ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('apparel_requests', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['apparel_id']);
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('apparel_id')
                  ->references('id')
                  ->on('apparels')
                  ->onDelete('cascade');
        });
    }
};
