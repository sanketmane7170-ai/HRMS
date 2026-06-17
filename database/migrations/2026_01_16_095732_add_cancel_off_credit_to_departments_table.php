<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('cancel_off_credit')
                  ->nullable()
                  ->after('budget');

            $table->decimal('cancel_off_amount', 10, 2)
                  ->nullable()
                  ->after('cancel_off_credit');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_off_credit',
                'cancel_off_amount',
            ]);
        });
    }
};
