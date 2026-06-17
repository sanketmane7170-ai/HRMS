<?php

use App\Enums\MartialStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->date('date_of_birth');
            $table->string('personal_email');
            $table->string('personal_phone');
            $table->string('address');
            $table->string('linkedin_url')->nullable();
            $table->string('skills')->nullable();
            $table->string('hobbies')->nullable();
            $table->enum('martial_status', ['married', 'single', 'divorced', 'widow', 'seperated']);
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->foreignId('country_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->unique()->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
