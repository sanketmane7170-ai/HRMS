<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_salary_components')) {
            Schema::create('ip_salary_components', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique(); // BASIC, HRA, CONVEYANCE, SPECIAL_ALLOWANCE, LTA, MEDICAL, EMPLOYER_PF, EMPLOYER_ESI, GRATUITY_PROVISION, EPF_EMPLOYEE, ESI_EMPLOYEE, PT, LWF_EMPLOYEE, TDS, ...
                $table->string('name');
                $table->enum('type', ['earning', 'deduction', 'employer_contribution']);
                $table->boolean('is_taxable')->default(true);
                $table->boolean('is_statutory')->default(false); // statutory components (PF/ESI/PT/LWF/TDS/Gratuity) are computed by the engine, not manually entered
                $table->boolean('is_part_of_ctc')->default(true);
                $table->boolean('considered_for_pf_wage')->default(false); // Basic + DA typically
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_salary_components');
    }
};
