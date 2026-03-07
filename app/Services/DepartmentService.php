<?php

namespace App\Services;

use App\Models\Department;
use App\Models\AcademicYear;
use App\Models\StudentMark;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Get result analysis for a department.
     */
    public function getResultAnalysis(Department $department, $academicYearId = null)
    {
        $academicYearId = $academicYearId ?? AcademicYear::active()->first()?->id;
        $subjectIds = $department->subjects()->pluck('id');

        return StudentMark::whereIn('subject_id', $subjectIds)
            ->where('academic_year_id', $academicYearId)
            ->select(
                'subject_id',
                DB::raw('AVG(score) as average_score'),
                DB::raw('COUNT(*) as entries_count'),
                DB::raw('MAX(score) as highest_score'),
                DB::raw('MIN(score) as lowest_score'),
                DB::raw("SUM(CASE WHEN score < 50 THEN 1 ELSE 0 END) as below_50"),
                DB::raw("SUM(CASE WHEN score >= 50 AND score < 75 THEN 1 ELSE 0 END) as bracket_50_75"),
                DB::raw("SUM(CASE WHEN score >= 75 AND score < 90 THEN 1 ELSE 0 END) as bracket_75_90"),
                DB::raw("SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as above_90")
            )
            ->groupBy('subject_id')
            ->with('subject')
            ->get();
    }

    /**
     * Get grading progress for teachers in the department.
     */
    public function getGradingProgress(Department $department, $academicYearId = null)
    {
        $academicYearId = $academicYearId ?? AcademicYear::active()->first()?->id;
        
        // This would involve checking teacher assignments vs marks entered
        // For now, a placeholder logic
        return [];
    }
}
