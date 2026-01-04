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
        // 1. Identify and remove duplicates (keep the most recent one)
        // Groups are identified by (student_id, assessment_template_id)
        
        $duplicates = DB::table('student_marks')
            ->select('student_id', 'assessment_template_id', DB::raw('COUNT(*) as count'))
            ->groupBy('student_id', 'assessment_template_id')
            ->having('count', '>', 1)
            ->get();
            
        foreach ($duplicates as $duplicate) {
            // Fetch all marks for this group, ordered by date desc (keep latest)
            $marks = DB::table('student_marks')
                ->where('student_id', $duplicate->student_id)
                ->where('assessment_template_id', $duplicate->assessment_template_id)
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->toArray();
                
            // Remove the first one (latest) from the delete list
            array_shift($marks);
            
            // Delete the rest
            if (!empty($marks)) {
                DB::table('student_marks')->whereIn('id', $marks)->delete();
            }
        }

        // 2. Add Unique Index
        Schema::table('student_marks', function (Blueprint $table) {
            $table->unique(['student_id', 'assessment_template_id'], 'sm_stud_templ_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropUnique('sm_stud_templ_unique');
        });
    }
};
