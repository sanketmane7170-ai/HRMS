<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appraisal_templates', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Filters (nullable = applies to all)
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();

            $table->enum('period_type', [
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'half_yearly',
                'yearly'
            ]);

            $table->boolean('is_active')->default(1);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appraisal_templates');
    }
};