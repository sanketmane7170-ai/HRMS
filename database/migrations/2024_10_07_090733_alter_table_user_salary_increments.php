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
        Schema::table('user_salary_increments', function (Blueprint $table) {
           
            $table->after('increment_date', function ($table) {
                $table->Integer('before_increment')->nullable()->default(null);
            });
             $table->after('increment', function ($table) {
                $table->Integer('after_increment')->nullable()->default(null);
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
