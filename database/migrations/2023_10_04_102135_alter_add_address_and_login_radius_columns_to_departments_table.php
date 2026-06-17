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
        Schema::table('departments', function (Blueprint $table) {
            $table->string('address')->after('manager_id')->nullable(true);
            $table->float('login_radius')->after('address')->nullable(true);
            $table->string('latitude')->after('login_radius')->nullable(true);
            $table->string('longitude')->after('latitude')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('login_radius');
            $table->dropColumn('address');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
