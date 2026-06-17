<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('employee_notice_periods')) {
            Schema::create('employee_notice_periods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('resignation_id')->constrained('resignations')->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->enum('status', ['active', 'completed', 'waived', 'shortened', 'extended'])->default('active');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_notice_periods');
    }
};
