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
        // Create assessment_templates table
        Schema::create('assessment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('term_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('assessment_type_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('weight', 5, 2); // Percentage (0-100)
            $table->decimal('max_score', 5, 2); // Auto-set to weight
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for faster queries
            $table->index(['academic_year_id', 'term_id']);
        });

        // Create pivot table for template assignments
        Schema::create('assessment_template_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_level_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combinations
            $table->unique(['assessment_template_id', 'grade_level_id', 'subject_id'], 'template_grade_subject_unique');
            
            // Indexes for faster queries
            $table->index(['grade_level_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_template_assignments');
        Schema::dropIfExists('assessment_templates');
    }
};
