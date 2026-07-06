<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce one promotion decision per student per source year at the
     * database level. A student is promoted out of a given academic year
     * exactly once (into the following year), so (student_id,
     * from_academic_year_id) is unique. The controller already de-duplicates
     * via updateOrCreate, but that is a select-then-insert and so cannot stop
     * a concurrent double-submit (double-click / two tabs / retry) from
     * inserting twice. This unique index makes the invariant structural.
     */
    public function up(): void
    {
        // Collapse any pre-existing duplicates first, otherwise adding the
        // unique index fails. Keep the most recent row (highest id) in each
        // (student, from_year) group — it reflects the latest decision.
        $keepIds = DB::table('student_promotions')
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('student_id', 'from_academic_year_id')
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            DB::table('student_promotions')
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        Schema::table('student_promotions', function (Blueprint $table) {
            $table->unique(
                ['student_id', 'from_academic_year_id'],
                'student_promotions_student_from_year_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_promotions', function (Blueprint $table) {
            $table->dropUnique('student_promotions_student_from_year_unique');
        });
    }
};
