<?php

namespace App\Services;

use App\Models\User;
use App\Models\Section;
use App\Models\Department;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\TeacherAssignment;
use App\Services\GradingService;
use Illuminate\Support\Facades\Cache;

class TeacherService
{
    /**
     * Get the homeroom section for a teacher (if any).
     */
    public function getHomeroomSection(User $user, $academicYearId = null)
    {
        $academicYearId = $academicYearId ?? AcademicYear::active()->first()?->id;

        return Section::with('gradeLevel')
            ->where('homeroom_teacher_id', $user->id)
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    /**
     * Check if a user is a homeroom teacher.
     */
    public function isHomeroomTeacher(User $user, $academicYearId = null): bool
    {
        return !is_null($this->getHomeroomSection($user, $academicYearId));
    }

    /**
     * Get the departments headed by the teacher.
     */
    public function getHeadedDepartments(User $user)
    {
        return Department::where('head_id', $user->id)->get();
    }

    /**
     * Check if a user is a department head.
     */
    public function isDepartmentHead(User $user): bool
    {
        return Department::where('head_id', $user->id)->exists();
    }

    /**
     * Get summary metrics for a teacher's dashboard.
     */
    public function getDashboardMetrics(User $user)
    {
        $activeYear = AcademicYear::active()->first();
        
        // Cache key includes date to refresh daily for schedule
        $date = now()->format('Y-m-d');
        return Cache::remember("teacher_metrics_{$user->id}_{$activeYear->id}_{$date}", 3600, function() use ($user, $activeYear) {
            $homeroom = $this->getHomeroomSection($user, $activeYear->id);
            
            $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $user->id)
                ->where('academic_year_id', $activeYear->id)
                ->get();
            
            $assignmentIds = $teacherAssignments->pluck('id');

            // Pending Assignments: Submissions that are submitted but not yet graded (score is null)
            $pendingAssignments = \App\Models\ActivitySubmission::whereIn('academic_activity_id', function($query) use ($assignmentIds) {
                    $query->select('id')->from('academic_activities')->whereIn('teacher_assignment_id', $assignmentIds);
                })
                ->where(function($query) {
                    $query->where('status', 'submitted')
                          ->orWhere(function($q) {
                              $q->whereNull('score')->whereNotNull('submitted_at');
                          });
                })
                ->count();

            // Today's Schedule
            $dayOfWeek = now()->format('l');
            $todaySchedule = \App\Models\Timetable::with(['section.gradeLevel', 'classPeriod', 'teacherAssignment.subject'])
                ->whereIn('teacher_assignment_id', $assignmentIds)
                ->where('day_of_week', $dayOfWeek)
                ->get()
                ->sortBy(function($item) {
                    return $item->classPeriod->start_time;
                })
                ->values();

            // Free Periods
            $totalPeriodsCount = \App\Models\ClassPeriod::where('is_break', false)->count();
            $freePeriodsCount = max(0, $totalPeriodsCount - $todaySchedule->count());

            return [
                'has_homeroom' => !is_null($homeroom),
                'homeroom_section' => $homeroom?->name,
                'homeroom_grade' => $homeroom?->gradeLevel?->name,
                'homeroom_student_count' => $homeroom?->enrollments()->where('status', 'active')->count() ?? 0,
                'is_dept_head' => $this->isDepartmentHead($user),
                'headed_departments' => $this->getHeadedDepartments($user)->pluck('name')->toArray(),
                'classes_count' => $teacherAssignments->count(),
                'pending_assignments_count' => $pendingAssignments,
                'free_periods_count' => $freePeriodsCount,
                'today_schedule' => $todaySchedule,
                'today_name' => $dayOfWeek,
            ];
        });
    }
    /**
     * Get performance distribution for a specific assignment and term.
     */
    public function getPerformanceDistribution(User $user, $assignmentId, $termId)
    {
        $assignment = TeacherAssignment::with(['section', 'subject'])->findOrFail($assignmentId);
        
        // Security check
        if ($assignment->teacher_id !== $user->id) {
            return null;
        }

        $term = Term::findOrFail($termId);
        $academicYear = AcademicYear::active()->first();
        $subject = $assignment->subject;
        $section = $assignment->section;

        if ($subject->is_elective) {
            $students = $section->students()
                ->whereHas('electives', function($q) use ($subject, $academicYear) {
                    $q->where('subject_id', $subject->id)
                      ->where('student_electives.academic_year_id', $academicYear->id);
                })
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->get();
        } else {
            $students = $section->students()
                ->wherePivot('academic_year_id', $academicYear->id)
                ->wherePivot('status', 'active')
                ->where('students.is_active', true)
                ->get();
        }

        $gradingService = app(GradingService::class);
        $batchResults = $gradingService->calculateSectionTotals($students, $term, $academicYear, collect([$subject]));
        
        $distribution = [
            '0-49' => 0,
            '50-74' => 0,
            '75-100' => 0,
            'total' => $students->count()
        ];

        foreach ($students as $student) {
            $score = $batchResults[$student->id]['marks'][$subject->id] ?? 0;
            if ($score <= 49) $distribution['0-49']++;
            elseif ($score <= 74) $distribution['50-74']++;
            else $distribution['75-100']++;
        }

        return $distribution;
    }

    /**
     * Get overall efficiency (percentage of students >= 75) for all teacher's classes in a term.
     */
    public function getOverallEfficiency(User $user, $termId, $subjectId = null)
    {
        $activeYear = AcademicYear::active()->first();
        $term = Term::findOrFail($termId);
        
        $query = TeacherAssignment::where('teacher_id', $user->id)
            ->where('academic_year_id', $activeYear->id);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $assignments = $query->with(['section', 'subject'])->get();

        $totalStudentsCount = 0;
        $totalPassedCount = 0;
        $gradingService = app(GradingService::class);

        foreach ($assignments as $assignment) {
            $section = $assignment->section;
            $subject = $assignment->subject;

            if ($subject->is_elective) {
                $students = $section->students()
                    ->whereHas('electives', function($q) use ($subject, $activeYear) {
                        $q->where('subject_id', $subject->id)
                          ->where('student_electives.academic_year_id', $activeYear->id);
                    })
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->get();
            } else {
                $students = $section->students()
                    ->wherePivot('academic_year_id', $activeYear->id)
                    ->wherePivot('status', 'active')
                    ->where('students.is_active', true)
                    ->get();
            }

            if ($students->isEmpty()) continue;

            $batchResults = $gradingService->calculateSectionTotals($students, $term, $activeYear, collect([$subject]));
            
            foreach ($students as $student) {
                $score = $batchResults[$student->id]['marks'][$subject->id] ?? 0;
                $totalStudentsCount++;
                if ($score >= 75) {
                    $totalPassedCount++;
                }
            }
        }

        return [
            'efficiency' => $totalStudentsCount > 0 ? round(($totalPassedCount / $totalStudentsCount) * 100, 1) : 0,
            'passed_count' => $totalPassedCount,
            'total_students' => $totalStudentsCount
        ];
    }
}
