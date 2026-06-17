<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('ip_bank_details')) {
            Schema::create('ip_bank_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->string('bank_name');
                $table->text('account_number'); // encrypted cast
                $table->string('ifsc', 11);
                $table->enum('account_type', ['savings', 'current'])->default('savings');
                $table->string('account_holder_name');
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ip_bank_details');
    }
};
