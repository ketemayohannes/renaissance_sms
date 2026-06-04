<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. DATA CLEANUP: Restore marks to their correct subjects
        // We identify marks where the teacher's assignment (section + teacher) 
        // points to a different subject than what is currently on the mark record.
        // This happened because the previous unique index was too narrow and 
        // the upsert logic didn't update the subject_id.

        $corruptedMarks = DB::table('student_marks as sm')
            ->join('teacher_assignments as ta', function($join) {
                $join->on('sm.teacher_id', '=', 'ta.teacher_id')
                     ->on('sm.section_id', '=', 'ta.section_id');
            })
            ->whereColumn('sm.subject_id', '!=', 'ta.subject_id')
            ->select('sm.id', 'ta.subject_id as correct_subject_id')
            ->get();

        foreach ($corruptedMarks as $mark) {
            DB::table('student_marks')
                ->where('id', $mark->id)
                ->update(['subject_id' => $mark->correct_subject_id]);
        }

        // 2. INDEX UPDATE
        Schema::table('student_marks', function (Blueprint $table) {
            // Drop flawed narrow indexes
            $table->dropUnique('sm_stud_templ_unique');
            
            // Check if this one exists before dropping
            if (DB::getDriverName() !== 'sqlite') {
                if (Schema::hasIndex('student_marks', 'student_component_unique')) {
                    $table->dropUnique('student_component_unique');
                }
            }

            // Create new robust unique index
            $table->unique(['student_id', 'assessment_template_id', 'subject_id'], 'sm_stud_templ_subj_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropUnique('sm_stud_templ_subj_unique');
            $table->unique(['student_id', 'assessment_template_id'], 'sm_stud_templ_unique');
        });
    }
};
