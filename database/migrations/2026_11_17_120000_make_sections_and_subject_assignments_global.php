<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sections and subject assignments (grade level ↔ subject) are no longer tied to an
     * academic year: the same sections and subject lists are reused every year. What
     * stays per year is who is in a section (student_enrollments.academic_year_id),
     * marks, attendance, terms and teacher assignments.
     */
    public function up(): void
    {
        // A grade could have the same subject assigned under several years; keep one row.
        $keep = DB::table('grade_level_subjects')
            ->selectRaw('MIN(id) as id')
            ->groupBy('grade_level_id', 'subject_id')
            ->pluck('id');
        DB::table('grade_level_subjects')->whereNotIn('id', $keep)->delete();

        // The new unique key is added before the old one is dropped: MySQL uses the old
        // key as the index backing the grade_level_id foreign key.
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->unique(['grade_level_id', 'subject_id'], 'gls_grade_subject_unique');
        });
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropUnique('gls_unique');
            if (Schema::hasIndex('grade_level_subjects', 'grade_level_subjects_academic_year_id_index')) {
                $table->dropIndex('grade_level_subjects_academic_year_id_index');
            }
        });
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            if (Schema::hasIndex('sections', 'sections_academic_year_id_index')) {
                $table->dropIndex('sections_academic_year_id_index');
            }
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
        });
    }

    /**
     * Restores the columns, assigning every row to the active academic year.
     */
    public function down(): void
    {
        $yearId = DB::table('academic_years')->where('is_active', true)->value('id')
            ?? DB::table('academic_years')->orderByDesc('id')->value('id');

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('grade_level_id');
        });
        DB::table('sections')->update(['academic_year_id' => $yearId]);
        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->index('academic_year_id');
        });

        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('subject_id');
        });
        DB::table('grade_level_subjects')->update(['academic_year_id' => $yearId]);
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->index('academic_year_id');
            $table->unique(['grade_level_id', 'subject_id', 'academic_year_id'], 'gls_unique');
        });
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropUnique('gls_grade_subject_unique');
        });
    }
};
