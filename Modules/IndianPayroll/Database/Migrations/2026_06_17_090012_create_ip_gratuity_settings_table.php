<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_gratuity_settings')) {
            Schema::create('ip_gratuity_settings', function (Blueprint $table) {
                $table->id();
                $table->date('effective_from');
                $table->decimal('exemption_ceiling', 14, 2)->default(2000000.00); // Section 10(10) lifetime exemption ceiling
                $table->integer('days_per_year_first_slab')->default(15); // 15/26 formula numerator
                $table->integer('divisor_days_per_month')->default(26);
                $table->integer('minimum_vesting_years')->default(5);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_gratuity_settings');
    }
};
