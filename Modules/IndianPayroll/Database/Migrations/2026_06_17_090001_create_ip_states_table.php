<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_states')) {
            Schema::create('ip_states', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code', 4)->unique(); // e.g. MH, KA, TN
                $table->enum('region_type', ['state', 'union_territory'])->default('state');
                $table->boolean('pt_applicable')->default(false); // not every state levies Professional Tax
                $table->boolean('lwf_applicable')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_states');
    }
};
