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
        Schema::create('subject_analysis_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            
            $table->text('range_0_49_remark')->nullable();
            $table->text('range_50_74_remark')->nullable();
            $table->text('range_75_100_remark')->nullable();
            $table->text('comparison_comment')->nullable();
            
            $table->timestamps();

            // Unique index to ensure only one report per class/term/year
            $table->unique(['teacher_assignment_id', 'term_id', 'academic_year_id'], 'sar_unique_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_analysis_reports');
    }
};
