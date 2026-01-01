<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            if (!Schema::hasIndex('student_marks', 'student_marks_academic_year_id_index')) {
                $table->index('academic_year_id', 'student_marks_academic_year_id_index');
            }
            if (!Schema::hasIndex('student_marks', 'student_marks_term_id_index')) {
                $table->index('term_id', 'student_marks_term_id_index');
            }
            if (!Schema::hasIndex('student_marks', 'student_marks_subject_id_index')) {
                $table->index('subject_id', 'student_marks_subject_id_index');
            }
            if (!Schema::hasIndex('student_marks', 'student_marks_section_id_index')) {
                $table->index('section_id', 'student_marks_section_id_index');
            }
            if (!Schema::hasIndex('student_marks', 'sm_student_template_idx')) {
                $table->index(['student_id', 'assessment_template_id'], 'sm_student_template_idx');
            }
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            if (!Schema::hasIndex('student_enrollments', 'student_enrollments_academic_year_id_index')) {
                $table->index('academic_year_id', 'student_enrollments_academic_year_id_index');
            }
            if (!Schema::hasIndex('student_enrollments', 'student_enrollments_section_id_index')) {
                $table->index('section_id', 'student_enrollments_section_id_index');
            }
            if (!Schema::hasIndex('student_enrollments', 'student_enrollments_status_index')) {
                $table->index('status', 'student_enrollments_status_index');
            }
        });

        Schema::table('grade_level_subjects', function (Blueprint $table) {
            if (!Schema::hasIndex('grade_level_subjects', 'grade_level_subjects_academic_year_id_index')) {
                $table->index('academic_year_id', 'grade_level_subjects_academic_year_id_index');
            }
            if (!Schema::hasIndex('grade_level_subjects', 'grade_level_subjects_sort_order_index')) {
                $table->index('sort_order', 'grade_level_subjects_sort_order_index');
            }
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
