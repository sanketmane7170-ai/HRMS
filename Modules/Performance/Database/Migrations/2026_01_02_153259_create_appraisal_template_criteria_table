<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appraisal_template_criteria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('template_id')
                  ->constrained('appraisal_templates')
                  ->cascadeOnDelete();

            $table->string('criteria_name');
            $table->text('description')->nullable();

            $table->integer('weight')->default(1);
            $table->integer('max_score')->default(5);

            $table->text('comments')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appraisal_template_criteria');
    }
};
