<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_salary_structure_templates')) {
            Schema::create('ip_salary_structure_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ip_salary_structure_template_components')) {
            Schema::create('ip_salary_structure_template_components', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('template_id');
                $table->foreign('template_id', 'ip_sstc_template_fk')->references('id')->on('ip_salary_structure_templates')->onDelete('cascade');
                $table->unsignedBigInteger('salary_component_id');
                $table->foreign('salary_component_id', 'ip_sstc_component_fk')->references('id')->on('ip_salary_components')->onDelete('cascade');
                $table->enum('calculation_type', ['flat', 'percentage_of_basic', 'percentage_of_ctc', 'remainder_of_ctc'])->default('flat');
                $table->decimal('value', 12, 2)->default(0); // flat amount, or percentage (e.g. 40.00 for 40%)
                $table->timestamps();

                $table->unique(['template_id', 'salary_component_id'], 'ip_sstc_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_salary_structure_template_components');
        Schema::dropIfExists('ip_salary_structure_templates');
    }
};
