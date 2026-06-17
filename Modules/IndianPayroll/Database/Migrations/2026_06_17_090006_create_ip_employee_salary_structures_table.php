<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_employee_salary_structures')) {
            Schema::create('ip_employee_salary_structures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('template_id')->nullable()->constrained('ip_salary_structure_templates')->onDelete('set null');
                $table->decimal('annual_ctc', 14, 2);
                $table->decimal('monthly_ctc', 12, 2);
                $table->date('effective_from');
                $table->date('effective_to')->nullable(); // null = currently active
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
            });
        }

        if (! Schema::hasTable('ip_employee_salary_structure_components')) {
            Schema::create('ip_employee_salary_structure_components', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('structure_id');
                $table->foreign('structure_id', 'ip_essc_structure_fk')->references('id')->on('ip_employee_salary_structures')->onDelete('cascade');
                $table->unsignedBigInteger('salary_component_id');
                $table->foreign('salary_component_id', 'ip_essc_component_fk')->references('id')->on('ip_salary_components')->onDelete('cascade');
                $table->decimal('monthly_amount', 12, 2);
                $table->decimal('annual_amount', 14, 2);
                $table->timestamps();

                $table->unique(['structure_id', 'salary_component_id'], 'ip_essc_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_employee_salary_structure_components');
        Schema::dropIfExists('ip_employee_salary_structures');
    }
};
