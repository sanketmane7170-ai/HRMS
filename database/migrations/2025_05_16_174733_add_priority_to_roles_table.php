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
        Schema::table('roles', function (Blueprint $table) {
            // Add priority column with default null
            $table->integer('priority')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop the priority column if rolling back
            $table->dropColumn('priority');
        });
    }
};
