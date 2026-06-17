<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_employee_profiles')) {
            Schema::create('ip_employee_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

                // Statutory identity
                $table->string('pan', 10)->nullable()->unique();
                $table->text('aadhaar')->nullable(); // encrypted cast — never store plaintext
                $table->string('uan', 12)->nullable()->unique();
                $table->string('pf_number')->nullable();
                $table->string('esi_number', 17)->nullable();
                $table->string('pt_enrollment_number')->nullable();

                // Work location (drives Professional Tax / LWF slabs)
                $table->foreignId('state_id')->nullable()->constrained('ip_states')->onDelete('set null');

                // Statutory applicability — decided once at onboarding, can be revised by HR
                $table->boolean('pf_applicable')->default(true);
                $table->boolean('pf_voluntary_above_ceiling')->default(false); // employee opts to contribute on full basic, not just the wage ceiling
                $table->boolean('esi_applicable')->default(false); // recomputed monthly from gross vs threshold, this is a manual override/lock
                $table->boolean('pt_applicable')->default(true);
                $table->boolean('lwf_applicable')->default(true);

                // Employment dates relevant to gratuity vesting / exit
                $table->date('date_of_joining');
                $table->date('date_of_exit')->nullable();
                $table->enum('exit_reason', ['resignation', 'termination', 'retirement', 'death', 'disablement'])->nullable();

                $table->enum('gender', ['male', 'female', 'other'])->nullable(); // PT slabs in some states differ by gender

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_employee_profiles');
    }
};
