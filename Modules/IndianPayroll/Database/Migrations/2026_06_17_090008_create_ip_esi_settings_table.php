<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_esi_settings')) {
            Schema::create('ip_esi_settings', function (Blueprint $table) {
                $table->id();
                $table->date('effective_from');
                $table->decimal('employee_rate', 5, 2)->default(0.75); // % of gross wage
                $table->decimal('employer_rate', 5, 2)->default(3.25); // % of gross wage
                $table->decimal('wage_threshold', 12, 2)->default(21000.00); // ESI applicable only if gross <= threshold
                $table->decimal('wage_threshold_disabled', 12, 2)->default(25000.00); // higher threshold for employees with disability
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_esi_settings');
    }
};
