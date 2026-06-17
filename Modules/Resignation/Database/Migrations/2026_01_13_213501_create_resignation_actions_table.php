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
        if (!Schema::hasTable('resignation_actions')) {
            Schema::create('resignation_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('resignation_id')->constrained('resignations')->onDelete('cascade');
                $table->foreignId('action_by')->constrained('users')->onDelete('cascade'); // Who performed the action
                $table->string('action_role'); // Role of the person acting (e.g., 'manager', 'admin', 'hr')
                $table->enum('action_type', ['approve', 'reject', 'hold', 'comment', 'recommend']);
                $table->text('comments')->nullable();
                $table->timestamp('action_date')->useCurrent();
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
        Schema::dropIfExists('resignation_actions');
    }
};
