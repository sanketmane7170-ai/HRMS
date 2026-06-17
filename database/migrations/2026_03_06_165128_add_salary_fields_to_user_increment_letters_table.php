<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_increment_letters', function (Blueprint $table) {
            $table->decimal('user_basic_salary', 10, 2)->nullable()->after('date');
            $table->decimal('user_transportation_allowances', 10, 2)->nullable()->after('user_basic_salary');
            $table->decimal('user_housing_allowances', 10, 2)->nullable()->after('user_transportation_allowances');
            $table->decimal('user_other_allowances', 10, 2)->nullable()->after('user_housing_allowances');
            $table->decimal('user_gross_salary', 10, 2)->nullable()->after('user_other_allowances');
            $table->text('remarks')->nullable()->after('user_gross_salary');
        });
    }

    public function down(): void
    {
        Schema::table('user_increment_letters', function (Blueprint $table) {
            $table->dropColumn([
                'user_basic_salary',
                'user_transportation_allowances',
                'user_housing_allowances',
                'user_other_allowances',
                'user_gross_salary',
                'remarks'
            ]);
        });
    }
};
