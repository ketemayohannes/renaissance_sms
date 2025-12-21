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
        Schema::table('student_marks', function (Blueprint $table) {
            $table->index('academic_year_id');
            $table->index('term_id');
            $table->index('subject_id');
            $table->index('section_id');
            $table->index(['student_id', 'assessment_template_id'], 'sm_student_template_idx');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->index('academic_year_id');
            $table->index('section_id');
            $table->index('status');
        });

        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->index('academic_year_id');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['term_id']);
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['section_id']);
            $table->dropIndex('sm_student_template_idx');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['section_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['sort_order']);
        });
    }
};
