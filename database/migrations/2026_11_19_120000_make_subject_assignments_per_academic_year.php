<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Subject assignments (grade level ↔ subject) belong to an academic year again, so a
     * year's list can change without rewriting earlier years' report cards and averages.
     * Sections stay shared across years.
     *
     * Every existing assignment is copied into every academic year, so nothing changes
     * for any year at the moment this runs.
     */
    public function up(): void
    {
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->foreignId('academic_year_id')->nullable()->after('subject_id')
                ->constrained()->cascadeOnDelete();
        });

        // Swap the unique key before copying rows into other years, or the old
        // (grade, subject) key rejects the copies. The new key is added before the old
        // one is dropped: MySQL uses the old key as the index backing the grade_level_id
        // foreign key.
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->unique(['grade_level_id', 'subject_id', 'academic_year_id'], 'gls_grade_subject_year_unique');
        });
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropUnique('gls_grade_subject_unique');
        });

        $yearIds = DB::table('academic_years')->orderBy('start_date')->orderBy('id')->pluck('id');

        if ($yearIds->isNotEmpty()) {
            $rows = DB::table('grade_level_subjects')->get();
            $now = now();

            // The existing rows become the first year's; the other years get copies.
            DB::table('grade_level_subjects')->update(['academic_year_id' => $yearIds->first()]);

            foreach ($yearIds->slice(1) as $yearId) {
                $copies = $rows->map(fn ($r) => [
                    'grade_level_id' => $r->grade_level_id,
                    'subject_id' => $r->subject_id,
                    'academic_year_id' => $yearId,
                    'is_required' => $r->is_required,
                    'sort_order' => $r->sort_order,
                    'created_at' => $r->created_at ?? $now,
                    'updated_at' => $now,
                ])->all();

                foreach (array_chunk($copies, 500) as $chunk) {
                    DB::table('grade_level_subjects')->insert($chunk);
                }
            }
        }
    }

    /**
     * Back to one shared list per grade: keeps the active year's assignments.
     */
    public function down(): void
    {
        $yearId = DB::table('academic_years')->where('is_active', true)->value('id')
            ?? DB::table('academic_years')->orderByDesc('start_date')->value('id');

        if ($yearId) {
            DB::table('grade_level_subjects')->where('academic_year_id', '!=', $yearId)->delete();
        }

        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->unique(['grade_level_id', 'subject_id'], 'gls_grade_subject_unique');
        });
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropUnique('gls_grade_subject_year_unique');
        });
        Schema::table('grade_level_subjects', function (Blueprint $table) {
            $table->dropColumn('academic_year_id');
        });
    }
};
