<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_promotion_letters', function (Blueprint $table) {
            $table->unsignedBigInteger('old_department_id')->nullable()->after('new_designation_id');
            $table->unsignedBigInteger('new_department_id')->nullable()->after('old_department_id');
            $table->text('remarks')->nullable()->after('new_department_id');
            $table->text('reason')->nullable()->after('remarks');

            // Optional: if you want foreign key constraints
            // $table->foreign('old_department_id')->references('id')->on('departments')->nullOnDelete();
            // $table->foreign('new_department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_promotion_letters', function (Blueprint $table) {
            if (Schema::hasColumn('user_promotion_letters', 'old_department_id')) {
                $table->dropColumn('old_department_id');
            }
            if (Schema::hasColumn('user_promotion_letters', 'new_department_id')) {
                $table->dropColumn('new_department_id');
            }
            if (Schema::hasColumn('user_promotion_letters', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('user_promotion_letters', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};
