<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_pf_settings')) {
            Schema::create('ip_pf_settings', function (Blueprint $table) {
                $table->id();
                $table->date('effective_from');
                $table->decimal('employee_rate', 5, 2)->default(12.00); // % of PF wage
                $table->decimal('employer_rate', 5, 2)->default(12.00); // % of PF wage (split between EPF + EPS)
                $table->decimal('eps_rate', 5, 2)->default(8.33); // % of PF wage, carved out of employer_rate, capped at eps wage ceiling
                $table->decimal('wage_ceiling', 12, 2)->default(15000.00); // statutory PF wage ceiling
                $table->decimal('eps_wage_ceiling', 12, 2)->default(15000.00);
                $table->decimal('admin_charges_rate', 5, 2)->default(0.50); // EPF admin charges, employer-borne, not employee-visible
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_pf_settings');
    }
};
