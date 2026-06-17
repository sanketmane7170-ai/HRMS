<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'ip_salary_components',
            'ip_pt_slabs',
            'ip_lwf_rules',
            'ip_income_tax_slabs',
            'ip_income_tax_surcharge_slabs',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'ip_salary_components',
            'ip_pt_slabs',
            'ip_lwf_rules',
            'ip_income_tax_slabs',
            'ip_income_tax_surcharge_slabs',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};
