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
        Schema::create('expense_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_name'); // Name of the document
            $table->string('document'); // Path or URL of the uploaded document
            $table->unsignedBigInteger('expense_id'); // Foreign key referencing expenses table
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_documents');
    }
};
