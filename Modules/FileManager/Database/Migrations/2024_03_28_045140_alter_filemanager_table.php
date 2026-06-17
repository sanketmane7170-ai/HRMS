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
        Schema::table('file_managers', function (Blueprint $table) {
            $table->renameColumn('user_id','employee_id');
            $table->string('file_type')->after('file_name');
            $table->integer('file_size')->after('file_type');
            $table->timestamp('upload_date')->useCurrent();
            $table->unsignedBigInteger('department_id')->after('file_desc')->nullable();
            $table->enum('file_status', ['Active', 'Deleted'])->after('file_size')->default('Active');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete(null);
            $table->foreign('employee_id')->references('id')->on('users')->onDelete(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_managers');
    }
};
