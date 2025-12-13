<?php

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

$year = AcademicYear::where('is_active', true)->first();
echo "Active Year: " . ($year ? $year->name . " (ID: {$year->id})" : "None") . "\n";

if ($year) {
    $count = DB::table('grade_level_subjects')->where('academic_year_id', $year->id)->count();
    echo "Total Assignments for Active Year: $count\n";

    $assignments = DB::table('grade_level_subjects')
        ->where('academic_year_id', $year->id)
        ->select('grade_level_id', DB::raw('count(*) as subject_count'))
        ->groupBy('grade_level_id')
        ->get();

    echo "Assignments per Grade Level:\n";
    foreach ($assignments as $a) {
        echo "- Grade Level ID {$a->grade_level_id}: {$a->subject_count} subjects\n";
    }
}
