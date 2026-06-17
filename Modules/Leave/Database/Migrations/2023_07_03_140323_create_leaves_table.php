<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\Leave\Enums\LeaveStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('total_leave_days');
            $table->boolean('is_half_day')->default(false);
            $table->string('reason');
            $table->string('remark')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default(LeaveStatus::Pending->value);
            $table->foreignId('leave_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaves');
    }
};
