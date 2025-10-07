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
        Schema::create('forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUuid('specialty_id')->nullable()->constrained('medical_specialties');
            $table->foreignUuid('doctor_id')->nullable()->constrained('doctors');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('form_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->integer('position')->default(0);
        });

        Schema::create('form_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignUuid('section_id')->nullable()->constrained('form_sections')->nullOnDelete();
            $table->string('label');
            $table->text('help_text')->nullable();
            $table->enum('type', ['single_choice', 'multi_choice', 'text', 'number', 'date', 'boolean']);
            $table->boolean('required')->default(false);
            $table->integer('position')->default(0);
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('question_id')->constrained('form_questions')->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->integer('position')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
        Schema::dropIfExists('form_sections');
        Schema::dropIfExists('form_questions');
        Schema::dropIfExists('question_options');
    }
};
