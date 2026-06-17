<?php

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
        Schema::create('user_dependent_documents', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('user_dependent_id'); // Foreign Key to user_documents table
            $table->string('document_name'); // Name of the dependent document
            $table->string('document'); // Path or name of the dependent document file
            $table->timestamps(); // Created at and Updated at columns

            // Define the foreign key constraint
            $table->foreign('user_dependent_id')
                ->references('id')
                ->on('user_dependents')
                ->onDelete('cascade'); // Delete dependent documents if the parent document is deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dependent_documents');
    }
};
