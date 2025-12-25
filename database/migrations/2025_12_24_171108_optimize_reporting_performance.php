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
        if (!Schema::hasIndex('sections', 'sections_grade_level_id_index')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->index('grade_level_id');
            });
        }

        if (!Schema::hasIndex('sections', 'sections_academic_year_id_index')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->index('academic_year_id');
            });
        }

        if (!Schema::hasIndex('student_term_records', 'str_student_term_academic_idx')) {
            Schema::table('student_term_records', function (Blueprint $table) {
                $table->index(['student_id', 'term_id', 'academic_year_id'], 'str_student_term_academic_idx');
            });
        }

        if (!Schema::hasIndex('student_attendances', 'sa_student_date_idx')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->index(['student_id', 'attendance_date'], 'sa_student_date_idx');
            });
        }

        if (!Schema::hasIndex('student_attendances', 'sa_section_date_idx')) {
            Schema::table('student_attendances', function (Blueprint $table) {
                $table->index(['section_id', 'attendance_date'], 'sa_section_date_idx');
            });
        }

        if (!Schema::hasIndex('assessment_templates', 'assessment_templates_academic_year_id_index')) {
            Schema::table('assessment_templates', function (Blueprint $table) {
                $table->index('academic_year_id');
            });
        }

        if (!Schema::hasIndex('audit_logs', 'audit_logs_created_at_index')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropIndex('sections_grade_level_id_index');
            $table->dropIndex('sections_academic_year_id_index');
        });

        Schema::table('student_term_records', function (Blueprint $table) {
            $table->dropIndex('str_student_term_academic_idx');
        });

        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropIndex('sa_student_date_idx');
            $table->dropIndex('sa_section_date_idx');
        });

        Schema::table('assessment_templates', function (Blueprint $table) {
            $table->dropIndex('assessment_templates_academic_year_id_index');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_created_at_index');
        });
    }
};
