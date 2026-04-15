<?php

namespace App\Actions\Teachers;

use App\Models\AcademicYear;
use App\Models\Section;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignTeacherAssignments
{
    /**
     * Assign teachers to sections and subjects.
     * Enforces the Pre-Primary/Kindergarten single-section rule.
     *
     * @param int $teacherUserId
     * @param array $assignmentsData
     * @param AcademicYear $academicYear
     * @return void
     * @throws ValidationException
     */
    public function execute(int $teacherUserId, array $assignmentsData, AcademicYear $academicYear): void
    {
        // Extract all unique section IDs the teacher is being assigned to
        $sectionIds = collect($assignmentsData)->flatMap(function ($assignment) {
            return $assignment['section_ids'] ?? [];
        })->unique()->toArray();
        $sections = Section::with('gradeLevel.division')->whereIn('id', $sectionIds)->get();

        $prePrimarySections = collect();
        foreach ($sections as $section) {
            $divisionName = strtolower($section->gradeLevel->division->name ?? '');
            if (str_contains($divisionName, 'pre-primary') || str_contains($divisionName, 'kindergarten') || str_contains($divisionName, 'kg')) {
                $prePrimarySections->push($section);
            }
        }

        // Rule 1: A Pre-Primary teacher can only be assigned to a single section per year
        if ($prePrimarySections->isNotEmpty() && $sections->count() > 1) {
            throw ValidationException::withMessages([
                'assignments' => 'A Pre-Primary (Kindergarten) teacher can only be assigned to a single section per academic year.'
            ]);
        }

        DB::transaction(function () use ($teacherUserId, $assignmentsData, $academicYear, $prePrimarySections) {
            // Clear existing assignments for this year
            TeacherAssignment::where('teacher_id', $teacherUserId)
                ->where('academic_year_id', $academicYear->id)
                ->delete();

            // Store new assignments
            foreach ($assignmentsData as $assignment) {
                foreach ($assignment['section_ids'] as $sectionId) {
                    TeacherAssignment::create([
                        'teacher_id' => $teacherUserId,
                        'section_id' => $sectionId,
                        'subject_id' => $assignment['subject_id'],
                        'academic_year_id' => $academicYear->id,
                    ]);
                }
            }

            // Rule 2: If assigned to Pre-Primary, automatically become the Homeroom Teacher
            if ($prePrimarySections->isNotEmpty()) {
                $section = $prePrimarySections->first();
                $section->update(['homeroom_teacher_id' => $teacherUserId]);
            }
        });
    }
}
