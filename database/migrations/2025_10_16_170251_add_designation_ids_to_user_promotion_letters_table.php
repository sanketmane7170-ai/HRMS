<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user_promotion_letters', function (Blueprint $table) {
            $table->unsignedBigInteger('old_designation_id')->nullable()->after('date');
            $table->unsignedBigInteger('new_designation_id')->nullable()->after('old_designation_id');

            // Optionally, if you want to enforce relationships
            $table->foreign('old_designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('new_designation_id')->references('id')->on('designations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('user_promotion_letters', function (Blueprint $table) {
            $table->dropForeign(['old_designation_id']);
            $table->dropForeign(['new_designation_id']);
            $table->dropColumn(['old_designation_id', 'new_designation_id']);
        });
    }
};
