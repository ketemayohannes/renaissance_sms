<?php

namespace App\Services;

use App\Models\User;
use App\Models\Section;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Cache;

class TeacherService
{
    /**
     * Get the homeroom section for a teacher (if any).
     */
    public function getHomeroomSection(User $user, $academicYearId = null)
    {
        $academicYearId = $academicYearId ?? AcademicYear::active()->first()?->id;

        return Section::where('homeroom_teacher_id', $user->id)
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
        
        return Cache::remember("teacher_metrics_{$user->id}_{$activeYear->id}", 3600, function() use ($user, $activeYear) {
            $homeroom = $this->getHomeroomSection($user, $activeYear->id);
            
            return [
                'has_homeroom' => !is_null($homeroom),
                'homeroom_section' => $homeroom?->name,
                'homeroom_student_count' => $homeroom?->enrollments()->where('status', 'active')->count() ?? 0,
                'is_dept_head' => $this->isDepartmentHead($user),
                'headed_departments' => $this->getHeadedDepartments($user)->pluck('name')->toArray(),
            ];
        });
    }
}
