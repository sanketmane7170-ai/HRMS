<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('country_name')->index();
            $table->string('currency_name');
            $table->string('currency_code', 3)->unique();
            $table->string('symbol')->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable(); // Stores exchange rate
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
