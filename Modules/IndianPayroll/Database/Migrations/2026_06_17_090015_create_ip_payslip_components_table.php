<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_payslip_components')) {
            Schema::create('ip_payslip_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payslip_id')->constrained('ip_payslips')->onDelete('cascade');
                $table->foreignId('salary_component_id')->constrained('ip_salary_components')->onDelete('cascade');
                $table->string('type', 25); // earning / deduction / employer_contribution — denormalized snapshot, immune to later catalog edits
                $table->decimal('amount', 12, 2);
                $table->timestamps();

                $table->unique(['payslip_id', 'salary_component_id'], 'ip_payslip_components_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_payslip_components');
    }
};
